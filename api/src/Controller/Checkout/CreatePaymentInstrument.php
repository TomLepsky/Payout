<?php

namespace App\Controller\Checkout;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\PaymentInstrument;
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
class CreatePaymentInstrument extends AbstractController
{
    public function __construct(
        private CheckoutApi $checkoutApi,
        private ValidatorInterface $validator,
        private PaymentService $paymentService,
        private SerializerInterface $serializer
    ) {}

    public function __invoke(Request $request) : PaymentInstrument
    {
        /** @var PaymentInstrumentInput $paymentInstrumentInput */
        $paymentInstrumentInput = $this->serializer->deserialize(
            $request->getContent(),
            PaymentInstrumentInput::class,
            'json',
            ['groups' => 'instrument:write']);
        $this->validator->validate($paymentInstrumentInput);

        $newPaymentInstrument = $this->paymentService->createPaymentInstrument($paymentInstrumentInput, $this->getUser());

        $manager = $this->getDoctrine()->getManager();
        /** @var PaymentInstrumentRepository $repository */
        $repository = $manager->getRepository(PaymentInstrument::class);
        $paymentInstrument = $repository->findOneBy(['fingerprint' => $newPaymentInstrument->getFingerprint()]);
        if ($paymentInstrument !== null) {
            $this->checkoutApi
                ->execute(new DeletePaymentInstrumentAction($newPaymentInstrument->getSource()))
                ->handleResponseErrors();
            throw new BadRequestException("Такой платёжный инструмент уже существует.");
        }

        $newPaymentInstrument
            ->setTitle($paymentInstrumentInput->title)
            ->setNote($paymentInstrumentInput->note);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($newPaymentInstrument);
        $manager->flush();

        return $newPaymentInstrument;
    }
}
