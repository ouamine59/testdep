<?php

namespace App\Tests\Entity ;

use App\Entity\Ads ;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdsTest extends KernelTestCase
{
    public function testValidEntity()
    {
        $user = new User();
        $user->setEmail('test@example.com')
             ->setPassword('password')
             ->setRoles(['ROLE_USER']);
        $ads = (new Ads())
            ->setUser($user)
            ->setTitle('aaaa')
            ->setDescription('zzzzzzaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')
            ->setPrice('1111')
            ->setZipCode(11111)
            ->setWidth(111)
            ->setlength(111)
            ->setHeight(11)
            ->setVerified(0);
        $validator = self::getContainer()->get('validator');
        $errors = $validator->validate($ads);

        // Vérifier qu'il n'y a pas d'erreurs
        $this->assertCount(0, $errors);
    }
}
