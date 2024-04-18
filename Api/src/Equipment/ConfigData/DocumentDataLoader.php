<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Document;

class DocumentDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $documentData) {
            if ($documentData['type'] !== 'document') {
                continue;
            }

            $document = $this->mechanicsRepository->findOneBy(['name' => $documentData['name']]);

            if ($document === null) {
                $document = new Document();
            } elseif (!$document instanceof Document) {
                $this->entityManager->remove($document);
                $document = new Document();
            }

            $document
                ->setName($documentData['name'])
                ->setContent($documentData['content'])
                ->setIsTranslated($documentData['isTranslated'])
                ->setCanShred($documentData['canShred']);
            $this->setMechanicsActions($document, $documentData);

            $this->entityManager->persist($document);
        }
        $this->entityManager->flush();
    }
}
