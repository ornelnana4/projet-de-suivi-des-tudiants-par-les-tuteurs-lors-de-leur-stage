<?php

namespace App\Controller;

use App\Repository\TuteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        TuteurRepository $tuteurRepository,
        SessionInterface $session
    ): Response {
        $email = $request->request->get('email');

        if ($request->isMethod('POST')) {
            // 1. Recherche du tuteur par email
            $tuteur = $tuteurRepository->findOneBy(['email' => $email]);

            if ($tuteur) {
                // 2. Stocker l'id du tuteur en session
                $session->set('tuteur_id', $tuteur->getId());

                // 3. Message + redirection vers /dashboard
                $this->addFlash('success', 'Connexion réussie.');
                return $this->redirectToRoute('app_dashboard');
            }

            // 4. Si aucun tuteur trouvé
            $this->addFlash('error', 'Identifiants invalides.');
        }

        return $this->render('security/login.html.twig', [
            'last_email' => $email,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('tuteur_id');
        $this->addFlash('success', 'Vous êtes déconnecté.');
        return $this->redirectToRoute('app_login');
    }
}
