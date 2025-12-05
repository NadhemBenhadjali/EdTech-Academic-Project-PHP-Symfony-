<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PublicCourseController extends AbstractController
{
    #[Route('/courses', name: 'public_course_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $courses = $em->getRepository(Course::class)->findAll();

        return $this->render('public_course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/course/{id}', name: 'public_course_show')]
    public function show(Course $course, EntityManagerInterface $em): Response
    {
        // By default, user is not enrolled
        $isEnrolled = false;

        $user = $this->getUser();
        if ($user) {
            // Look for an existing purchase for this user & course
            $existing = $em->getRepository(Purchase::class)->findOneBy([
                'user'   => $user,
                'course' => $course,
            ]);

            $isEnrolled = $existing !== null;
        }

        return $this->render('public_course/show.html.twig', [
            'course'      => $course,
            'is_enrolled' => $isEnrolled,
        ]);
    }

    #[Route('/course/{id}/enroll', name: 'public_course_enroll')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function enroll(Course $course, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Check if already purchased
        $existing = $em->getRepository(Purchase::class)->findOneBy([
            'user' => $user,
            'course' => $course,
        ]);

        if (!$existing) {
            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setCourse($course);
            $purchase->setDate(new \DateTimeImmutable());

            $em->persist($purchase);
            $em->flush();

            $this->addFlash('success', 'You are enrolled in this course.');
        } else {
            $this->addFlash('info', 'You are already enrolled in this course.');
        }

        return $this->redirectToRoute('student_courses');
    }
}
