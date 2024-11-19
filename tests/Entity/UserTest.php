<?php

namespace App\Tests\Entity ;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function testValidEntity()
    {
        $user = new User();
        $user->setEmail('tes1t@example.com')
        ->setRoles(['ROLE_USER'])
        ->setPassword('pasAswordA#1')
        ->setUsername('eeeAAAZzzzzzz')
        ->setPhone(0153403020)
        ->setFirstName('aaaffffffff')
        ->setLastName('aaaaffffff');
        $validator = self::getContainer()->get('validator');
        $errors = $validator->validate($user);

        // Vérifier qu'il n'y a pas d'erreurs
        $this->assertCount(0, $errors);
    }
}
