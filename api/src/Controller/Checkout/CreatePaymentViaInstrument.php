<?php

namespace App\Controller\Checkout;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Config;
use App\Entity\Payment;
use App\Entity\PaymentInstrument;
use App\Model\SourcePaymentInput;
use App\Repository\PaymentInstrumentRepository;
use App\Service\Payment\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class CreatePaymentViaInstrument extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private PaymentService $paymentService,
        private SerializerInterface $serializer
    ) {}

    public function __invoke(Request $request) : Payment
    {
        $this->denyAccessUnlessGranted(Config::CHIEF_ACCOUNTANT);

        $content = $request->getContent();
        $paymentInput = $this->serializer->deserialize(
            $content,
            SourcePaymentInput::class,
            'json',
            ['groups' => 'payment:write']
        );
        $this->validator->validate($paymentInput);
        /** @var SourcePaymentInput $paymentInput */
        $paymentInput = PaymentService::subsidiaryCoinConverter($paymentInput, PaymentService::TO_SUBSIDIARY);
        $this->paymentService->restrict($paymentInput);

        $manager = $this->getDoctrine()->getManager();
        /** @var PaymentInstrumentRepository $repository */
        $repository = $manager->getRepository(PaymentInstrument::class);
        $paymentInstrument = $repository->findOneBy(['source' => $paymentInput->source]);

        if ($paymentInstrument === null) {
            throw new NotFoundHttpException("Платёжного инструмента с таким идентификатором нету $paymentInput->source");
        }

        if ($paymentInstrument->getHide()) {
            throw new BadRequestException("Эта карта заархивирована, но вы можете её снова разархивировать.");
        }

        $payment = $this->paymentService->processPaymentViaInstrument($paymentInput, $paymentInstrument, $this->getUser());

        $paymentInstrument->addPayment($payment);
        $manager->persist($paymentInstrument);
        $manager->flush();

        return $payment;
    }
}
