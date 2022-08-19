<?php

namespace App\Controller\Checkout;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Payment;
use App\Entity\PaymentInstrument;
use App\Model\CardPaymentInput;
use App\Model\PaymentInstrumentInput;
use App\Repository\PaymentInstrumentRepository;
use App\Service\Checkout\Action\DeletePaymentInstrumentAction;
use App\Service\Checkout\CheckoutApi;
use App\Service\Payment\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class CreatePaymentViaCard extends AbstractController
{
    public function __construct(
        private CheckoutApi $checkoutApi,
        private ValidatorInterface $validator,
        private PaymentService $paymentService,
        private SerializerInterface $serializer
    ) {}

    public function __invoke(Request $request) : Payment
    {
        $content = $request->getContent();

        $paymentInput = $this->serializer->deserialize(
            $content,
            CardPaymentInput::class,
            'json',
            ['groups' => 'payment:write']
        );
        $this->validator->validate($paymentInput);
        /** @var CardPaymentInput $paymentInput */
        $paymentInput = PaymentService::subsidiaryCoinConverter($paymentInput, PaymentService::TO_SUBSIDIARY);
        $this->paymentService->restrict($paymentInput);

        $paymentInstrumentInput = $this->serializer->deserialize(
            $content,
            PaymentInstrumentInput::class,
            'json',
            ['groups' => 'instrument:write']
        );
        $newPaymentInstrument = $this->paymentService->createPaymentInstrument($paymentInstrumentInput, $this->getUser());

        $manager = $this->getDoctrine()->getManager();
        /** @var PaymentInstrumentRepository $repository */
        $repository = $manager->getRepository(PaymentInstrument::class);
        $paymentInstrument = $repository->findOneBy(['fingerprint' => $newPaymentInstrument->getFingerprint()]);

        if ($paymentInstrument === null) {
            $paymentInstrument = $newPaymentInstrument;
        } else {
            $this->checkoutApi
                ->execute(new DeletePaymentInstrumentAction($newPaymentInstrument->getSource()))
                ->handleResponseErrors();
            if ($paymentInstrument->getHide()) {
                throw new BadRequestException("Эта карта заархивирована, но вы можете её снова разархивировать.");
            }
        }

        $payment = $this->paymentService->processPaymentViaInstrument($paymentInput, $paymentInstrument, $this->getUser());

        $paymentInstrument->addPayment($payment);
        $manager->persist($paymentInstrument);
        $manager->flush();

        return $payment;
    }
}
