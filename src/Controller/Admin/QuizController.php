<?php

namespace App\Controller\Admin;

use App\Entity\Quiz;
use App\Entity\Course;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/quiz')]
#[IsGranted('ROLE_ADMIN')]
final class QuizController extends AbstractController
{
    #[Route('/', name: 'admin_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): Response
    {
        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizRepository->findAll(),
        ]);
    }

    #[Route('/course/{id}/new', name: 'admin_quiz_new_for_course', methods: ['GET', 'POST'])]
    public function newForCourse(
        Course $course,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $quiz = new Quiz();
        $quiz->setCourse($course);

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();

            return $this->redirectToRoute('admin_course_show', ['id' => $course->getId()]);
        }

        return $this->render('quiz/new.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
            'course' => $course,
        ]);
    }

    #[Route('/{id}', name: 'admin_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Quiz $quiz,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_course_show', [
                'id' => $quiz->getCourse()->getId(),
            ]);
        }

        return $this->render('quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_quiz_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Quiz $quiz,
        EntityManagerInterface $em
    ): Response {
        $courseId = $quiz->getCourse()->getId();

        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($quiz);
            $em->flush();
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
    }
}
