<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

class FrontController extends AbstractController
{
    public function header(Request $request): Response
    {
        // some logic to determine the $locale
        $locale = $request->getLocale();

        return $this->render('frontend/_header.html.twig', [
            'locale' => $locale
        ]);
    }

    #[Route(path: '/{_locale}', name: 'home', defaults: ['_locale' => 'fr'], requirements: ['_locale' => 'en|fr'])]
    public function index(Request $request): Response
    {
        return $this->render('frontend/home.html.twig', [
            // 'locale' => $locale,
        ]);
    }

    #[Route(path: '/{_locale}/curriculum-vitae', name: 'cv', defaults: ['_locale' => 'fr'], requirements: ['_locale' => 'en|fr'])]
    public function cv(): Response
    {
        return $this->render('frontend/cv.html.twig');
    }

    #[Route(path: '{_locale}/contact', name: 'contact', defaults: ['_locale' => 'fr'], requirements: ['_locale' => 'en|fr'])]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $names = $request->get('names');
            $email = $request->get('email');
            $project = $request->get('project');

            // SENDING EMAIL TO ADMIN
            $emailBody = '
                <div style="padding-bottom: 30px; font-size: 17px;">
                    <strong>NOUVEAU GOMBO,</strong>
                </div>
                <div style="padding-bottom: 30px">
                    <p>Bonne nouvelle, on vient de recevoir une nouvelle demande.</p>
                    <h3>Détails</h3>
                    <ul>
                        <li><strong>Date :</strong> '.date('l jS \of F Y').'</li>
                        <li><strong>Heure :</strong> '.date('h:i:s A').'</li>
                        <li><strong>Client :</strong> '.$names.'</li>
                        <li><strong>Email :</strong> '.$email.'</li>
                        <li><strong>Projet :</strong></li>
                    </ul>
                    <p>'.$project.'</p>
                </div>';
            $template1 = (new TemplatedEmail())
                ->from('contact@moussa-fofana.com')
                ->to('mfofana@aguimaagency.com')
                ->subject('NOUVEAU PROJET DE DEV')
                ->htmlTemplate('front/email.html.twig')
                ->context([
                    'emailBody' => $emailBody,
                ]);
            $mailer->send($template1);
            
            // SENDING EMAIL TO CLIENT
            $emailBody = '
                <div style="padding-bottom: 30px; font-size: 17px;">
                    <strong>Cher Client,</strong>
                </div>
                <div style="padding-bottom: 30px">
                    <p>Merci de nous avoir soumis votre projet.</p>
                    <p>Il sera traité, et vous recevrez une réponse dans les meilleurs délais.</p>
                </div>';
            $template2 = (new TemplatedEmail())
                ->from('contact@moussa-fofana.com')
                ->to($email)
                ->subject('ACCUSÉ DE RECEPTION')
                ->htmlTemplate('front/email.html.twig')
                ->context([
                    'emailBody' => $emailBody,
                ]);
            
            try {
                $mailer->send($template2);
                $this->addFlash('msgNotice', 'Thank you for submitting your project to us. <br>It will be processed, and you will receive an answer as soon as possible.');
            }
            catch (Exception $e) {
                $this->addFlash('msgNotice', 'Caught exception: ',  $e->getMessage(), "\n");
            }
        }
        return $this->redirectToRoute('home');
    }

    public function footer(): Response
    {
        return $this->render('frontend/_footer.html.twig');
    }
}
