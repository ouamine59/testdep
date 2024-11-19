<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\MediaObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Vich\UploaderBundle\Handler\UploadHandler;

class MediaObjectController extends AbstractController
{
    #[Route('/api/upload', name: 'app_upload_image', methods: ['POST'])]
    public function upload(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine,
        UploadHandler $uploadHandler
    ): Response {
        try{
        // Récupérer l'ID de l'annonce depuis le formulaire
        $adsId = $request->request->get('ads');
        if (!$adsId) {
            return new JsonResponse(['error' => 'L\'ID de l\'annonce est requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer l'entité Ads correspondante
        $ads = $doctrine->getRepository(Ads::class)->find($adsId);
        if (!$ads) {
            return new JsonResponse(['error' => 'Annonce non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Récupérer le fichier téléchargé
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'Fichier non fourni'], Response::HTTP_BAD_REQUEST);
        }

        $mediaObject = new MediaObject();
        $mediaObject->setFile($file);
        $mediaObject->setAds($ads);

        // Valider l'objet
        $errors = $validator->validate($mediaObject);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Enregistrer l'objet MediaObject
        $uploadHandler->upload($mediaObject, 'file');
        $entityManager->persist($mediaObject);
        $entityManager->flush();

        return new JsonResponse(
            ['message' => 'Image associée avec succès à l\'annonce.'],
            Response::HTTP_CREATED
        );
    } catch (\Exception $e) {
        return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }
}
