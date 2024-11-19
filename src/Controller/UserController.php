<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AdsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/user/register', name: 'app_user_register')]
    public function index(EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        try {


            $data = $request->getContent();
            // Traite les données (par exemple, décoder le JSON si nécessaire)
            $jsonData = json_decode($data, true);
            if ($jsonData === null) {
                return new JsonResponse(['result' => 'Invalid JSON format'], Response::HTTP_BAD_REQUEST);
            }
            if (!isset($jsonData['email'], $jsonData['password'], $jsonData['username'], $jsonData['phone'], $jsonData['firstName'], $jsonData['lastName'])) {
                return new JsonResponse(['result' => 'Data missing'], Response::HTTP_BAD_REQUEST);
            }
            $client = new User();
            $client->setEmail($jsonData['email']);
            $client->setRoles(["ROLE_USER"]);
            $hashedPassword = $passwordHasher->hashPassword($client, $jsonData['password']);
            $client->setPassword($hashedPassword);
            $client->setUserName($jsonData['username']);
            $client->setPhone($jsonData['phone']);
            $client->setFirstName($jsonData['firstName']);
            $client->setLastName($jsonData['lastName']);
            $errors = $validator->validate($client);

            $errors = $validator->validate($client);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return new JsonResponse(
                    ['result' => 'Validation failed', 'errors' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $entityManager->persist($client);
            $entityManager->flush();
            return new JsonResponse(
                ['result' => 'User registered successfully'],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/api/user/delete/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MediaObjectRepository $mediaObjectRepository,
        AdsRepository $adsRepository
    ) {
        try {


            $user = $userRepository->find($id);

            if ($user) {
                // Récupérer toutes les annonces de l'utilisateur
                $adsList = $adsRepository->findBy(['user' => $user]);

                foreach ($adsList as $ad) {
                    $ad->removeReporting($user);
                    $user->removeIsFavorite($ad);

                    $mediaObjects = $mediaObjectRepository->findBy(['ads' => $ad]);
                    foreach ($mediaObjects as $mediaObject) {
                        $entityManager->remove($mediaObject);
                    }
                    $entityManager->remove($ad);
                }




                // Supprimer l'utilisateur
                $entityManager->remove($user);
                $entityManager->flush();

                return new JsonResponse(
                    ['result' => 'User and related ads deleted successfully'],
                    Response::HTTP_OK
                );
            } else {
                return new JsonResponse(
                    ['result' => 'User not found'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user/admin/delete/{id}', name: 'app_user_admin_delete', methods: ['DELETE'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function deleteByAdmin(
        int $id,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MediaObjectRepository $mediaObjectRepository,
        AdsRepository $adsRepository
    ) {
        try {
            $user = $userRepository->find($id);

            if ($user) {
                // Récupérer toutes les annonces de l'utilisateur
                $adsList = $adsRepository->findBy(['user' => $user]);

                foreach ($adsList as $ad) {
                    $ad->removeReporting($user);
                    $user->removeIsFavorite($ad);

                    $mediaObjects = $mediaObjectRepository->findBy(['ads' => $ad]);
                    foreach ($mediaObjects as $mediaObject) {
                        $entityManager->remove($mediaObject);
                    }
                    $entityManager->remove($ad);
                }
                $entityManager->remove($user);
                $entityManager->flush();

                return new JsonResponse(
                    ['result' => 'User and related ads deleted successfully'],
                    Response::HTTP_OK
                );
            } else {
                return new JsonResponse(
                    ['result' => 'User not found'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    #[Route('/api/user/check-email/{email}', name: 'app_user_check_email', methods: ['GET'])]
    public function checkEmail(
        string $email,
        UserRepository $userRepository
    ) {
        try {
            $user = $userRepository->findBy(['email' => $email]);

            if ($user) {
                return new JsonResponse(
                    ['result' => 'Email  find'],
                    Response::HTTP_FOUND
                );
            } else {
                return new JsonResponse(
                    ['result' => 'User not found'],
                    Response::HTTP_OK
                );
            }
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/api/user/check-username/{username}', name: 'app_user_check_username', methods: ['GET'])]
    public function checkUserName(
        string $username,
        UserRepository $userRepository
    ) {
        try {
            $user = $userRepository->findBy(['userName' => $username]);

            if ($user) {
                return new JsonResponse(
                    ['result' => 'User  find'],
                    Response::HTTP_FOUND
                );
            } else {
                return new JsonResponse(
                    ['result' => 'User not found'],
                    Response::HTTP_OK
                );
            }
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user/favorite-ads/{adsId}/{userId}', name: 'app_user_favorite_ads', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
    public function favorite(
        int $adsId,
        int $userId,
        AdsRepository $adsRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $user = $userRepository->find($userId);
            if (!$user) {
                return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            $ads = $adsRepository->find($adsId);
            if (!$ads) {
                return new JsonResponse(['message' => 'Ad not found'], Response::HTTP_NOT_FOUND);
            }
            if ($user->getIsFavorite()->contains($ads)) {
                $user->removeIsFavorite($ads);
                $message = 'Ad removed from favorites';
            } else {
                $user->addIsFavorite($ads);
                $message = 'Ad added to favorites';
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['message' => $message], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user/update/{id}', name: 'app_user_update', methods: ['PUT'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
    public function update(
        int $id,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        try {
            // Rechercher l'utilisateur par son ID
            $client = $entityManager->getRepository(User::class)->find($id);

            // Vérifier si l'utilisateur existe
            if (!$client) {
                return new JsonResponse(
                    ['result' => 'User not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Décoder les données JSON envoyées dans la requête
            $data = json_decode($request->getContent(), true);

            // Vérifier la présence des clés nécessaires dans les données JSON
            if (!$data || !isset($data['email'],
                $data['password'],
                $data['username'],
                $data['phone'],
                $data['firstName'],
                $data['lastName'])) {
                return new JsonResponse(
                    ['result' => 'Invalid data provided'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Mettre à jour les données de l'utilisateur
            $client->setEmail($data['email']);
            $client->setRoles($data['roles'] ?? $client->getRoles()); // Garde les rôles existants s'ils ne sont pas fournis
            $client->setUserName($data['username']);
            $client->setPhone($data['phone']);
            $client->setFirstName($data['firstName']);
            $client->setLastName($data['lastName']);

            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($client, $data['password']);
            $client->setPassword($hashedPassword);

            // Validation des données mises à jour
            $errors = $validator->validate($client);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(
                    ['result' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Persister et sauvegarder les changements
            $entityManager->flush();

            return new JsonResponse(
                ['result' => 'User updated successfully'],
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
