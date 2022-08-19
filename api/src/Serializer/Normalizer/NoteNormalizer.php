<?php

namespace App\Serializer\Normalizer;

use App\Entity\Note;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class NoteNormalizer implements DenormalizerAwareInterface, ContextAwareDenormalizerInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'NOTE_DENORMALIZER_ALREADY_CALLED';

    public function __construct(private Security $security) {}

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []) : bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return Note::class === $type;
    }

    public function denormalize($data, string $type, string $format = null, array $context = []) : Note
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var Note $note */
        $note = $this->denormalizer->denormalize($data, $type, $format, $context);
        if (isset($context['groups']) && in_array('note:write', $context['groups'])) {
            $note->setOwner($this->security->getUser());
        }
        $note->setLastEditor($this->security->getUser());

        return $note;
    }
}
