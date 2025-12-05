<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DevController extends AbstractController
{
    #[Route('/create-admin', name: 'app_create_admin')]
    public function createAdmin(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Check if admin already exists to avoid duplicates
        $existing = $em->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
        if ($existing) {
            return new Response('Admin already exists', 200);
        }

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setName('Admin');

        // Plain password you want to use to log in:
        $plainPassword = 'admin123';

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Give admin role
        $user->setRoles(['ROLE_ADMIN']);

        $em->persist($user);
        $em->flush();

        return new Response('Admin user created with email admin@example.com and password '.$plainPassword);
    }

    #[Route('/create-teacher', name: 'app_create_teacher')]
    public function createTeacher(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Check if teacher already exists to avoid duplicates
        $existing = $em->getRepository(User::class)->findOneBy(['email' => 'teacher@example.com']);
        if ($existing) {
            return new Response('Teacher already exists', 200);
        }

        $user = new User();
        $user->setEmail('teacher@example.com');
        $user->setName('Teacher');

        // Plain password you want to use to log in:
        $plainPassword = 'teacher123';

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Teacher should also behave like a student, so give both roles
        $user->setRoles(['ROLE_TEACHER', 'ROLE_STUDENT']);

        $em->persist($user);
        $em->flush();

        return new Response('Teacher user created with email teacher@example.com and password '.$plainPassword);
    }
}
