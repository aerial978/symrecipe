<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Afficher & traiter le formulaire de demande de réinitialisation du mot de passe.
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }

        return $this->render('pages/reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Page de confirmation après qu'un utilisateur ait demandé la réinitialisation de son mot de passe.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Générez un faux jeton si l'utilisateur n'existe pas ou si quelqu'un accède directement à cette page.
        // Cela empêche d'exposer si oui ou non un utilisateur a été trouvé avec l'adresse e-mail donnée ou non.
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('pages/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Valide et traite l'URL de réinitialisation sur laquelle l'utilisateur a cliqué dans son e-mail.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, string $token = null): Response
    {
        if ($token) {
            // Nous stockons le jeton en session et le supprimons de l'URL, pour éviter que l'URL 
            // ne soit chargée dans un navigateur et que le jeton ne soit potentiellement divulgué au JavaScript tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }
            // Traitement validation token
        $token = $this->getTokenFromSession();
        // Si token non valide
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }
        
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Le jeton est valide ; permettre à l'utilisateur de changer son mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Un jeton de réinitialisation de mot de passe ne doit être utilisé qu'une seule fois, supprimez-le.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Hachez le plainpassword et définissez-le.
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // La session est nettoyée après la modification du mot de passe.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('security.login');
        }

        return $this->render('pages/reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Ne pas révéler si un compte d'utilisateur a été trouvé ou non.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // Si vous voulez dire à l'utilisateur pourquoi un e-mail de réinitialisation 
            // n'a pas été envoyé, décommentez les lignes ci-dessous et changez la redirection en 'app_forgot_password_request'.
            // Attention : Cela peut révéler si un utilisateur est enregistré ou non.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('michel@gmail.com', 'Michel'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('pages/reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Stockez l'objet jeton dans la session pour le récupérer dans la route de vérification des e-mails.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
