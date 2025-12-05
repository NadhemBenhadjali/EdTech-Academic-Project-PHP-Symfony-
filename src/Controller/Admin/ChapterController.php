<?php

namespace App\Controller\Admin;

use App\Entity\Chapter;
use App\Entity\Course;
use App\Form\ChapterType;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/chapter')]
#[IsGranted('ROLE_ADMIN')]
final class ChapterController extends AbstractController
{
    #[Route('/', name: 'admin_chapter_index', methods: ['GET'])]
    public function index(ChapterRepository $chapterRepository): Response
    {
        return $this->render('chapter/index.html.twig', [
            'chapters' => $chapterRepository->findAll(),
        ]);
    }

    #[Route('/course/{id}/new', name: 'admin_chapter_new_for_course', methods: ['GET', 'POST'])]
    public function newForCourse(
        Course $course,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $chapter = new Chapter();
        $chapter->setCourse($course);

        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($chapter);
            $em->flush();

            return $this->redirectToRoute('admin_course_show', ['id' => $course->getId()]);
        }

        return $this->render('chapter/new.html.twig', [
            'chapter' => $chapter,
            'form' => $form,
            'course' => $course,
        ]);
    }

    #[Route('/{id}', name: 'admin_chapter_show', methods: ['GET'])]
    public function show(Chapter $chapter): Response
    {
        return $this->render('chapter/show.html.twig', [
            'chapter' => $chapter,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_chapter_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Chapter $chapter,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_course_show', [
                'id' => $chapter->getCourse()->getId(),
            ]);
        }

        return $this->render('chapter/edit.html.twig', [
            'chapter' => $chapter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_chapter_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Chapter $chapter,
        EntityManagerInterface $em
    ): Response {
        $courseId = $chapter->getCourse()->getId();

        if ($this->isCsrfTokenValid('delete' . $chapter->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($chapter);
            $em->flush();
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
    }
}
