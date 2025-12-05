<?php

namespace App\Controller\Teacher;

use App\Entity\Attachment;
use App\Entity\Course;
use App\Form\AttachmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teacher/attachment')]
#[IsGranted('ROLE_TEACHER')]
final class AttachmentController extends AbstractController
{
    #[Route('/course/{id}/new', name: 'teacher_attachment_new_for_course', methods: ['GET', 'POST'])]
    public function newForCourse(
        Course $course,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        $attachment = new Attachment();
        $attachment->setCourse($course);

        $form = $this->createForm(AttachmentType::class, $attachment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($attachment);
            $em->flush();

            return $this->redirectToRoute('teacher_course_show', [
                'id' => $course->getId(),
            ]);
        }

        return $this->render('teacher/attachment/new.html.twig', [
            'attachment' => $attachment,
            'course'     => $course,
            'form'       => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'teacher_attachment_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Attachment $attachment,
        EntityManagerInterface $em
    ): Response {
        $course = $attachment->getCourse();
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        $form = $this->createForm(AttachmentType::class, $attachment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('teacher_course_show', [
                'id' => $course->getId(),
            ]);
        }

        return $this->render('teacher/attachment/edit.html.twig', [
            'attachment' => $attachment,
            'course'     => $course,
            'form'       => $form,
        ]);
    }

    #[Route('/{id}', name: 'teacher_attachment_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Attachment $attachment,
        EntityManagerInterface $em
    ): Response {
        $course = $attachment->getCourse();
        $this->denyAccessUnlessGrantedToOwnCourse($course);

        if ($this->isCsrfTokenValid('delete'.$attachment->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($attachment);
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
