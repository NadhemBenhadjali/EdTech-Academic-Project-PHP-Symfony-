<?php

namespace App\Controller\Admin;

use App\Entity\Attachment;
use App\Entity\Course;
use App\Form\AttachmentType;
use App\Repository\AttachmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/attachment')]
#[IsGranted('ROLE_ADMIN')]
final class AttachmentController extends AbstractController
{
    #[Route('/', name: 'admin_attachment_index', methods: ['GET'])]
    public function index(AttachmentRepository $attachmentRepository): Response
    {
        return $this->render('attachment/index.html.twig', [
            'attachments' => $attachmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_attachment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $attachment = new Attachment();
        $form = $this->createForm(AttachmentType::class, $attachment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($attachment);
            $em->flush();

            return $this->redirectToRoute('admin_attachment_index');
        }

        return $this->render('attachment/new.html.twig', [
            'attachment' => $attachment,
            'form' => $form,
        ]);
    }

    // "Add attachment" starting from a course context
    #[Route('/course/{id}/new', name: 'admin_attachment_new_for_course', methods: ['GET', 'POST'])]
    public function newForCourse(
        Course $course,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $attachment = new Attachment();
        $attachment->setCourse($course);

        $form = $this->createForm(AttachmentType::class, $attachment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($attachment);
            $em->flush();

            return $this->redirectToRoute('admin_course_show', [
                'id' => $course->getId(),
            ]);
        }

        return $this->render('attachment/new.html.twig', [
            'attachment' => $attachment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_attachment_show', methods: ['GET'])]
    public function show(Attachment $attachment): Response
    {
        return $this->render('attachment/show.html.twig', [
            'attachment' => $attachment,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_attachment_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Attachment $attachment,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(AttachmentType::class, $attachment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // If you edit from inside a course, redirect back there if possible
            if ($attachment->getCourse()) {
                return $this->redirectToRoute('admin_course_show', [
                    'id' => $attachment->getCourse()->getId(),
                ]);
            }

            return $this->redirectToRoute('admin_attachment_index');
        }

        return $this->render('attachment/edit.html.twig', [
            'attachment' => $attachment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_attachment_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Attachment $attachment,
        EntityManagerInterface $em
    ): Response {
        $course = $attachment->getCourse();

        if ($this->isCsrfTokenValid('delete'.$attachment->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($attachment);
            $em->flush();
        }

        if ($course) {
            return $this->redirectToRoute('admin_course_show', [
                'id' => $course->getId(),
            ]);
        }

        return $this->redirectToRoute('admin_attachment_index');
    }
}
