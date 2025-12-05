<?php

namespace App\Controller;

use App\Entity\Purchase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class StudentController extends AbstractController
{
    #[Route('/my-courses', name: 'student_courses')]
    public function myCourses(): Response
    {
        $user = $this->getUser();
        // thanks to the relation, we can use getPurchases()
        $purchases = $user->getPurchases();

        return $this->render('student/my_courses.html.twig', [
            'purchases' => $purchases,
        ]);
    }
}
