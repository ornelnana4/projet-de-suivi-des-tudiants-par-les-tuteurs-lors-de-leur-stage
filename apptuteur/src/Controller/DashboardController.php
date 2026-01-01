<?php

namespace App\Controller;

use App\Entity\Tuteur;
use App\Repository\TuteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(SessionInterface $session, TuteurRepository $tuteurRepository): Response
    {
        $tuteurId = $session->get('tuteur_id');

        if (!$tuteurId) {
            $this->addFlash('error', 'Vous devez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $tuteur = $tuteurRepository->find($tuteurId);

        if (!$tuteur) {
            $this->addFlash('error', 'Tuteur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'tuteur' => $tuteur,
        ]);
    }
}
