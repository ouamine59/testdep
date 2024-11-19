<?php

namespace App\Tests\Controller;

use App\Entity\Ads;
use App\Entity\User;
use App\Entity\MediaObject;
use PHPUnit\Framework\TestCase;
use App\Repository\AdsRepository;
use App\Controller\UserController;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends TestCase
{
    private $entityManagerMock;
    private $validatorMock;
    private $requestMock;
    private $passwordHasherMock;
    private $userRepositoryMock;
    private $mediaObjectRepositoryMock;
    private $adsRepositoryMock;

    protected function setUp(): void
    {
        // Mock de EntityManagerInterface
        $this->entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock de ValidatorInterface
        $this->validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock de UserPasswordHasherInterface
        $this->passwordHasherMock = $this->getMockBuilder(UserPasswordHasherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock de Request
        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Mock de EntityManagerInterface
        $this->entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock de UserRepository
        $this->userRepositoryMock = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock de MediaObjectRepository
        $this->mediaObjectRepositoryMock = $this->getMockBuilder(MediaObjectRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock de AdsRepository
        $this->adsRepositoryMock = $this->getMockBuilder(AdsRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRegisterUserSuccess()
    {
        // Configuration du mock du Request pour simuler une requête JSON valide
        $this->requestMock->method('getContent')->willReturn(json_encode([
            'email' => 'test@example.com',
            'roles' => ['ROLE_USER'],
            'password' => 'password123',
            'username' => 'testuser',
            'phone' => '1234567890',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]));

        // Configuration du mock du Validator pour retourner aucune erreur
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());

        // Configuration du mock du password hasher
        $this->passwordHasherMock->method('hashPassword')->willReturn('hashed_password');

        // Configuration du mock du EntityManager
        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');

        // Instancier le contrôleur
        $userController = new UserController();

        // Appeler la méthode index du contrôleur
        $response = $userController->index(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->passwordHasherMock
        );

        // Vérifier la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'User registered successfully']),
            $response->getContent()
        );
    }




    public function testDeleteUserSuccess()
    {
        // Mock d'un utilisateur
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock d'une liste d'annonces associées à l'utilisateur
        $adsMock = $this->getMockBuilder(Ads::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock des objets média associés à l'annonce
        $mediaObjectMock = $this->getMockBuilder(MediaObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configuration des mocks pour les dépendances
        $this->userRepositoryMock->method('find')->willReturn($userMock);
        $this->adsRepositoryMock->method('findBy')->willReturn([$adsMock]);
        $this->mediaObjectRepositoryMock->method('findBy')->willReturn([$mediaObjectMock]);

        // Attentes pour les appels aux méthodes remove et flush
        $this->entityManagerMock->expects($this->exactly(3))->method('remove');
        $this->entityManagerMock->expects($this->once())->method('flush');

        // Instancier le contrôleur
        $userController = new UserController();

        // Appeler la méthode delete
        $response = $userController->delete(
            1,
            $this->entityManagerMock,
            $this->userRepositoryMock,
            $this->mediaObjectRepositoryMock,
            $this->adsRepositoryMock
        );

        // Vérifier la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'User and related ads deleted successfully']),
            $response->getContent()
        );
    }

    public function testDeleteUserNotFound()
    {
        // Configuration pour un utilisateur non trouvé
        $this->userRepositoryMock->method('find')->willReturn(null);

        // Instancier le contrôleur
        $userController = new UserController();

        // Appeler la méthode delete
        $response = $userController->delete(
            999,
            $this->entityManagerMock,
            $this->userRepositoryMock,
            $this->mediaObjectRepositoryMock,
            $this->adsRepositoryMock
        );

        // Vérifier la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'User not found']),
            $response->getContent()
        );
    }

    public function testDeleteUserDatabaseError()
    {
        // Mock d'un utilisateur existant
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configuration pour que le repository retourne un utilisateur
        $this->userRepositoryMock->method('find')->willReturn($userMock);

        // Simuler une exception lors de l'appel à flush()
        $this->entityManagerMock->method('flush')->willThrowException(new \Exception('Database error'));

        // Instancier le contrôleur
        $userController = new UserController();

        // Appeler la méthode delete
        $response = $userController->delete(
            1,
            $this->entityManagerMock,
            $this->userRepositoryMock,
            $this->mediaObjectRepositoryMock,
            $this->adsRepositoryMock
        );

        // Vérifier la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Database error', $response->getContent());
    }






    public function testDeleteByAdminSuccess()
    {
        // Mock d'un utilisateur
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock d'une liste d'annonces associées à l'utilisateur
        $adsMock = $this->getMockBuilder(Ads::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock des objets média associés à l'annonce
        $mediaObjectMock = $this->getMockBuilder(MediaObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configuration des mocks pour les dépendances
        $this->userRepositoryMock->method('find')->willReturn($userMock);
        $this->adsRepositoryMock->method('findBy')->willReturn([$adsMock]);
        $this->mediaObjectRepositoryMock->method('findBy')->willReturn([$mediaObjectMock]);

        // Attentes pour les appels aux méthodes remove et flush
        $this->entityManagerMock->expects($this->exactly(3))->method('remove');
        $this->entityManagerMock->expects($this->once())->method('flush');

        // Instancier le contrôleur
        $userController = new UserController();

        // Appeler la méthode deleteByAdmin
        $response = $userController->deleteByAdmin(
            1,
            $this->entityManagerMock,
            $this->userRepositoryMock,
            $this->mediaObjectRepositoryMock,
            $this->adsRepositoryMock
        );

        // Vérifier la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'User and related ads deleted successfully']),
            $response->getContent()
        );
    }

    public function testDeleteByAdminUserNotFound()
    {
        // Configuration pour un utilisateur non trouvé
        $this->userRepositoryMock->method('find')->willReturn(null);

        // Instancier le contrôleur
        $userController = new UserController();

        // Appeler la méthode deleteByAdmin
        $response = $userController->deleteByAdmin(
            999,
            $this->entityManagerMock,
            $this->userRepositoryMock,
            $this->mediaObjectRepositoryMock,
            $this->adsRepositoryMock
        );

        // Vérifier la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'User not found']),
            $response->getContent()
        );
    }

    public function testDeleteByAdminDatabaseError()
    {
        // Mock d'un utilisateur existant
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configuration pour que le repository retourne un utilisateur
        $this->userRepositoryMock->method('find')->willReturn($userMock);

        // Simuler une exception lors de l'appel à flush()
        $this->entityManagerMock->method('flush')->willThrowException(new \Exception('Database error'));

        // Instancier le contrôleur
        $userController = new UserController();

        // Appeler la méthode deleteByAdmin
        $response = $userController->deleteByAdmin(
            1,
            $this->entityManagerMock,
            $this->userRepositoryMock,
            $this->mediaObjectRepositoryMock,
            $this->adsRepositoryMock
        );

        // Vérifier la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Database error', $response->getContent());
    }
}
