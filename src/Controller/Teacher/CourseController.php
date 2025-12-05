<?php

namespace App\Controller\Teacher;

use App\Entity\Course;
use App\Form\TeacherCourseType;
use App\Repository\CourseRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teacher/course')]
#[IsGranted('ROLE_TEACHER')]
final class CourseController extends AbstractController
{
    #[Route('/', name: 'teacher_course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository): Response
    {
        $teacher = $this->getUser();

        $courses = $courseRepository->findBy(['teacher' => $teacher]);

        return $this->render('teacher/course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/new', name: 'teacher_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $course = new Course();
        $course->setTeacher($this->getUser());

        $form = $this->createForm(TeacherCourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($course);
            $em->flush();

            return $this->redirectToRoute('teacher_course_index');
        }

        return $this->render('teacher/course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'teacher_course_show', methods: ['GET'])]
    public function show(
        Course $course,
        PurchaseRepository $purchaseRepository
    ): Response {
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        $purchases = $purchaseRepository->findBy(['course' => $course]);

        return $this->render('teacher/course/show.html.twig', [
            'course'     => $course,
            'purchases'  => $purchases,
        ]);
    }

    #[Route('/{id}/edit', name: 'teacher_course_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Course $course,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        $form = $this->createForm(TeacherCourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('teacher_course_index');
        }

        return $this->render('teacher/course/edit.html.twig', [
            'course' => $course,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'teacher_course_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Course $course,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($course);
            $em->flush();
        }

        return $this->redirectToRoute('teacher_course_index');
    }

    private function denyAccessUnlessGrantedToOwnCourse(Course $course): void
    {
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('This course does not belong to you.');
        }
    }
}
