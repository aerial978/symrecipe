<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact.index')]
    public function index(EntityManagerInterface $manager, MailerInterface $mailer, Request $request): Response
    {
        $contact = new Contact();

        if($this->getUser()) {
            $contact->setFullName($this->getUser()->getFullName())
                ->setEmail($this->getUser()->getEmail());
        }

        $contactForm = $this->createForm(ContactType::class, $contact);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $contact = $contactForm->getData();

            $manager->persist($contact);
            $manager->flush();

            $email = (new TemplatedEmail())
            ->from($contact->getEmail())
            ->to('admin@symrecipe.com')
            ->subject($contact->getSubject())
            ->htmlTemplate('emails/contact.html.twig')
            ->context([
                'contact' => $contact
            ]);

        $mailer->send($email);

            $this->addFlash(
                'success',
                'Your message was sent successfully !'
            );

            return $this->redirectToRoute('contact.index');

        }
        
        return $this->render('pages/contact/index.html.twig', [
            'contactForm' => $contactForm->createView(),
        ]);
    }
}
