<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(
        CategoryRepository $categoryRepository,
        CourseRepository $courseRepository,
        UserRepository $userRepository
    ): Response
    {
        // Use repository count method to get totals
        $categoriesCount = (int) $categoryRepository->count([]);
        $coursesCount = (int) $courseRepository->count([]);
        $usersCount = (int) $userRepository->count([]);

        return $this->render('admin/index.html.twig', [
            'categories_count' => $categoriesCount,
            'courses_count' => $coursesCount,
            'users_count' => $usersCount,
        ]);
    }

    #[Route('/admin/metrics', name: 'admin_metrics', methods: ['GET'])]
    public function metrics(
        CategoryRepository $categoryRepository,
        CourseRepository $courseRepository,
        UserRepository $userRepository
    ): JsonResponse
    {
        $data = [
            'categories_count' => (int) $categoryRepository->count([]),
            'courses_count' => (int) $courseRepository->count([]),
            'users_count' => (int) $userRepository->count([]),
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        return new JsonResponse($data);
    }
}