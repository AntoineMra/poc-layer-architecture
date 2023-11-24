<?php

namespace App\Service\Rh;

use App\Entity\MediaObject;
use Doctrine\ORM\EntityManagerInterface;

class ContractGenerator implements ContractGeneratorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManagerInterface)
    {
    }
    public function generate(): void
    {
        $pdfFile = new MediaObject();

        $pdfFile->setFile(new \SplFileInfo(__DIR__ . '/../../public/pdf/contrat.pdf'));
        $pdfFile->setMimeType('application/pdf');
        $pdfFile->setOriginalName('contrat.pdf');
        $pdfFile->setPath('pdf/contrat.pdf');
        $pdfFile->setSize(0);
        $pdfFile->setCreatedAt(new \DateTimeImmutable());
        $pdfFile->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManagerInterface->persist($pdfFile);
        $this->entityManagerInterface->flush();
    }
}
