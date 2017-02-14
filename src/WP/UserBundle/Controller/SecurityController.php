<?php

namespace WP\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as Controller;
use Symfony\Component\HttpFoundation\Request;
use WP\UserBundle\Entity\User;
use WP\UserBundle\Form\UserType;


class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('wp_tournament_homepage');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $user = new User();
        $registerMsg = null;
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->get('form.factory')->create(UserType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $existEmail = $em->getRepository('WPUserBundle:User')->findOneByEmail($user->getEmail());
            $existUsername = $em->getRepository('WPUserBundle:User')->findOneByUsername($user->getUsername());

            if($existEmail == null) {
                if($existUsername == null) {
                    $user->setEnabled(1);

                    $em->persist($user);
                    $em->flush();

                    $request->getSession()->getFlashBag()->add('notice', 'Vous êtes bien inscrit, vous pouvez maintenant vous connecter.');

                    return $this->redirectToRoute('fos_user_security_login');
                }
                else {
                    $registerMsg = 'Pseudo déjà utilisé.';
                }
            }
            else {
                $registerMsg = 'Email déjà utilisé.';
            }
        }

        return $this->render('WPUserBundle:Security:login.html.twig', array(
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
            'registerMsg' => $registerMsg
        ));
    }
}