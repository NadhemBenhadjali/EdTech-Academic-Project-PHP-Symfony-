<?php

namespace App\Controller\Teacher;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Form\QuizType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teacher/quiz')]
#[IsGranted('ROLE_TEACHER')]
final class QuizController extends AbstractController
{
    #[Route('/course/{id}/new', name: 'teacher_quiz_new_for_course', methods: ['GET', 'POST'])]
    public function newForCourse(
        Course $course,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        $quiz = new Quiz();
        $quiz->setCourse($course);

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();

            return $this->redirectToRoute('teacher_course_show', [
                'id' => $course->getId(),
            ]);
        }

        return $this->render('teacher/quiz/new.html.twig', [
            'quiz'   => $quiz,
            'course' => $course,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'teacher_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Quiz $quiz,
        EntityManagerInterface $em
    ): Response {
        $course = $quiz->getCourse();
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('teacher_course_show', [
                'id' => $course->getId(),
            ]);
        }

        return $this->render('teacher/quiz/edit.html.twig', [
            'quiz'   => $quiz,
            'course' => $course,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'teacher_quiz_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Quiz $quiz,
        EntityManagerInterface $em
    ): Response {
        $course = $quiz->getCourse();
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($quiz);
            $em->flush();
        }

        return $this->redirectToRoute('teacher_course_show', [
            'id' => $course->getId(),
        ]);
    }

    private function denyAccessUnlessGrantedToOwnCourse(Course $course): void
    {
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('This course does not belong to you.');
        }
    }
}
