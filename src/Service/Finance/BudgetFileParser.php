<?php

namespace App\Service\Finance;

use App\Entity\BankExtraction;
use App\Entity\BankTranslation;
use App\Entity\Budget;
use App\Entity\Enum\TransactionStatus;
use App\Entity\Enum\TransactionType;
use App\Entity\Transaction;
use App\Repository\BankTranslationRepository;
use Doctrine\DBAL\Driver\Mysqli\Initializer\Charset;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\Finder\Finder;

class BudgetFileParser implements BudgetFileParserInterface
{
    public function __construct(
        private readonly BankTranslationRepository $bankTranslationRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * This function aims to parse a bank extraction csv file to import transactions | Warning this only Works with Credit Mutuel formated files
     * @throws UnavailableStream
     * @throws Exception
     */
    public function parse(BankExtraction $bankExtraction): array
    {

        if ($bankExtraction?->getMediaObject() === null) {
            throw new \LogicException("The file is missing");
        }

        $finder = new Finder();
        $finder->in(__DIR__.'/../../public/media');
        $finder->name($bankExtraction->getMediaObject()->filePath);
        $finder->files();

        foreach ($finder as $file) {
            $csv = Reader::createFromPath($file->getRealPath())
                ->setHeaderOffset(0)
                ->setDelimiter(';')
            ;
            $csv->includeInputBOM();
            CharsetConverter::addBOMSkippingTo($csv);
            return $this->getTranslatedTransactions($csv, $bankExtraction->getBudget());
        }

        throw new \LogicException("The file is missing");
    }

    /**
     * @throws Exception
     */
    private function getTranslatedTransactions(Reader $csv, Budget $budget): array
    {
        $validatedTransactions = [];
        $draftObject = [];
        $records = $csv->getRecords();

        foreach ($records as $record) {
            $transaction = new Transaction();

            if ($record['Cr?dit'] !== "") {
                $transaction->setType(TransactionType::Income);
                $transaction->setAmount((int)$record['Cr?dit']);
            } else {
                $transaction->setType(TransactionType::Expense);
                $transaction->setAmount(abs((int)$record['D?bit']));
            }

            $transaction->setDate($record['Date']);
            $transaction->setBudget($budget);

            $formatedLabel = $this->formatLabel($record['Libell?']);
            $bankTranslation = $this->matchingTranslation($formatedLabel);
            if ($bankTranslation !== null) {
                $transaction->setLabel($bankTranslation->getCustomLabel());
                $transaction->setCategory($bankTranslation->getCategory());
                $transaction->setStatus(TransactionStatus::Validated);

                $validatedTransactions[] = $transaction;
            } else {
                $draftObject[] = [
                    'translation' => $this->createTranslation($formatedLabel),
                    'transaction' => $transaction
                ];
            }


        }

        return [
            'validatedTransactions' => $validatedTransactions,
            'draftObject' => $draftObject
        ];
    }

    private function formatLabel(string $label): string
    {
        $arrayWords = explode(" ", $label);

        if($arrayWords[0] === "PAIEMENT") {
            $arrayWords = array_slice($arrayWords, 3);
        }
        if($arrayWords[0] === "VIR") {
            $arrayWords = array_slice($arrayWords, 1);
        }
        if(end($arrayWords) === "Carte") {
            $arrayWords = array_slice($arrayWords, -2);
        }

        return implode(" ", $arrayWords);
    }

    private function matchingTranslation(string $label): ?BankTranslation
    {
        $bankTranslation = $this->bankTranslationRepository->isLabelParsed($label, TransactionStatus::Validated);

        return $bankTranslation ?? null;
    }

    private function createTranslation(string $label): BankTranslation
    {
        $isLabelParsed = $this->bankTranslationRepository->isLabelParsed($label, TransactionStatus::Draft);

        if ($isLabelParsed !== null) {
            return $isLabelParsed;
        }

        $bankTranslation = new BankTranslation();
        $bankTranslation->setBankLabel($label);
        $bankTranslation->setStatus(TransactionStatus::Draft);

        $this->entityManager->persist($bankTranslation);
        $this->entityManager->flush();

        return $bankTranslation;
    }
}
