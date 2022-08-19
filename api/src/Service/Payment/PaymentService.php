<?php

namespace App\Service\Payment;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use App\Config;
use App\Entity\Note;
use App\Entity\Payment;
use App\Entity\PaymentInstrument;
use App\Model\Card;
use App\Model\PaymentInput;
use App\Model\PaymentInstrumentInput;
use App\Model\Solvent;
use App\Model\Token;
use App\Service\Checkout\Action\CreatePaymentInstrumentAction;
use App\Service\Checkout\Action\CreateCardTokenAction;
use App\Service\Checkout\Action\SourcePayoutAction;
use App\Service\Checkout\CheckoutApi;
use LogicException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class PaymentService
{
    public const TO_SUBSIDIARY = 1;

    public const FROM_SUBSIDIARY = 2;

    private const SUBSIDIARY_COIN = [
        'USD' => 100,
        'RUB' => 100,
        'EUR' => 100,
        'UAH' => 100
    ];

    private const CURRENCY_SYMBOL = [
        'USD' => "$",
        'RUB' => "\u{20BD}",
        'EUR' => "\u{20AC}",
        'UAH' => "\u{20B4}",
    ];

    public function __construct(private CheckoutApi $api) {}

    public function createPaymentInstrument(PaymentInstrumentInput $instrumentInput, UserInterface $owner) : PaymentInstrument
    {
        $card = new Card();
        $card->number = $instrumentInput->number;
        $card->expiryMonth = $instrumentInput->expiryMonth;
        $card->expiryYear = $instrumentInput->expiryYear;

        /** @var Token $token */
        $token = $this->api
            ->execute(new CreateCardTokenAction($card))
            ->handleResponseErrors()
            ->getModelFromResponse();

        /** @var PaymentInstrument $paymentInstrument */
        $paymentInstrument = $this->api
            ->execute(new CreatePaymentInstrumentAction($token))
            ->handleResponseErrors()
            ->getModelFromResponse();
        $paymentInstrument
            ->setFirstName($instrumentInput->firstName)
            ->setLastName($instrumentInput->lastName)
            ->setOwner($owner);

        return $paymentInstrument;
    }

    public function processPaymentViaInstrument(PaymentInput $paymentInput, PaymentInstrument $paymentInstrument, UserInterface $owner) : Payment
    {
        /** @var Payment $payment */
        $payment = $this->api
            ->execute(new SourcePayoutAction((int)$paymentInput->amount, $paymentInput->currency, $paymentInstrument))
            ->handleResponseErrors()
            ->getModelFromResponse();

        $payment
            ->setOwner($owner)
            ->setAmount($paymentInput->amount)
            ->setCurrency($paymentInput->currency);

        if ($paymentInput->note !== null) {
            $payment->addNote(
                (new Note())
                    ->setContent($paymentInput->note)
                    ->setOwner($owner)
                    ->setLastEditor($owner)
            );
        }

        return $payment;
    }

    public function restrict(PaymentInput $paymentInput) : self
    {
        if (Config::CURRENCY_LIMIT[$paymentInput->currency] < $paymentInput->amount) {
            throw new ValidationException(new ConstraintViolationList([new ConstraintViolation(
                message: "Сумма перевода не должна быть больше " . Config::CURRENCY_LIMIT[$paymentInput->currency] / self::SUBSIDIARY_COIN[$paymentInput->currency] . " $paymentInput->currency",
                messageTemplate: '',
                parameters: [],
                root: null,
                propertyPath: 'amount',
                invalidValue: $paymentInput->amount
            )]));
        }

        return $this;
    }

    public static function subsidiaryCoinConverter(Solvent $solvent, int $mode = self::TO_SUBSIDIARY) : Solvent
    {
        if (in_array($solvent->getCurrency(), array_keys(self::SUBSIDIARY_COIN))) {
            $clonedSolvent = clone $solvent;
            if ($mode === self::TO_SUBSIDIARY) {
                $amount = $clonedSolvent->getAmount() * self::SUBSIDIARY_COIN[$solvent->getCurrency()];
                $fraction = fmod($amount, 1);
                if ($fraction !== 0.0) {
                    throw new LogicException("Разменная часть не может быть больше 2 знаков");
                }
                $clonedSolvent->setAmount($amount);
            } elseif ($mode === self::FROM_SUBSIDIARY) {
                $clonedSolvent->setAmount($clonedSolvent->getAmount() / self::SUBSIDIARY_COIN[$solvent->getCurrency()]);
            }

            return $clonedSolvent;
        }
        throw new LogicException("No available currency to convert.");
    }

    public static function createFingerPrint(int $cardNumber, int $expiryMonth, int $expiryYear) : string
    {
        return hash('sha256', "$cardNumber.$expiryMonth.$expiryYear");
    }
}
