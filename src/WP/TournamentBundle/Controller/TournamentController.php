<?php

namespace WP\TournamentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WP\TournamentBundle\Entity\Event;
use WP\TournamentBundle\Form\EventType;
use WP\TournamentBundle\Entity\Form;
use WP\UserBundle\Entity\User;
use WP\TournamentBundle\Entity\Inscription;
use WP\TournamentBundle\Form\FormType;
use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TournamentController extends Controller {

    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $events = $em->getRepository('WPTournamentBundle:Event')->getNextEvent();
        
        $event = $events[0];

        return $this->render('WPTournamentBundle:Tournament:index.html.twig', array(
                    'event' => $event
        ));
    }

    public function eventsAction($page) {
        $em = $this->getDoctrine()->getManager();

        $nbPerPage = 1;

        $events = $em->getRepository('WPTournamentBundle:Event')->getListEvents($page, $nbPerPage);

        $nbPages = ceil(count($events) / $nbPerPage);

        if (count($events) != 0) {
            if ($page > $nbPages || $page < 1) {
                throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
            }
        }

        return $this->render('WPTournamentBundle:Tournament:all_event.html.twig', array(
                    'page' => $page,
                    'nbPages' => $nbPages,
                    'events' => $events
        ));
    }

    public function eventAction($id) {
        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository('WPTournamentBundle:Event')->find($id);
        $user = $this->getUser();
        
        $ins = $em->getRepository('WPTournamentBundle:Inscription')->findOneBy(['user'=>$user,'event'=>$event]);
        
        return $this->render('WPTournamentBundle:Tournament:event.html.twig', array(
                    'event' => $event,
                    'ins' => $ins
        ));
    }
    
    public function eventInsAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('WPTournamentBundle:Event')->find($id);
        $user = $this->getUser();
        
        $ins = new Inscription();
        
        $ins->setEvent($event);
        $ins ->setUser($user);
        $ins->setInscrit(1);
        $em->persist($ins);
        $em->flush();
        $request->getSession()->getFlashBag()->add('notice', 'Votre inscription a bien été pris en compte.');
        return $this->redirectToRoute('wp_tournament_event',['id'=>$id]);
    }

    public function contactAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $msg = new Form();
        if ($this->getUser() != null) {
            $msg->setPseudo($this->getUser()->getUsername());
            $msg->setMail($this->getUser()->getEmail());
        }
        $form = $this->get('form.factory')->create(FormType::class, $msg);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $msg->setIsread(0);
            $em->persist($msg);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Le message a bien été envoyé.');

            return $this->redirectToRoute('wp_tournament_contact');
        }


        return $this->render('WPTournamentBundle:Tournament:contact.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function userAction (Request $request) {

        $user = $this->getUser();


        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('WPTournamentBundle:Tournament:user.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function adminEventAction() {
        $em = $this->getDoctrine()->getManager();

        $events = $em->getRepository('WPTournamentBundle:Event')->findAll();
        return $this->render('WPTournamentBundle:Tournament:adminEvent.html.twig', array(
                    'events' => $events
        ));
    }

    public function adminEventAddAction(Request $request) {
        $event = new Event();

        $formBuilder = $this->get('form.factory')->createBuilder(EventType::class, $event);
        $form = $formBuilder->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('wp_tournament_adminEvent');
        }

        return $this->render('WPTournamentBundle:Tournament:adminEventAdd.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function adminEventEditAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository('WPTournamentBundle:Event')->find($id);

        $formBuilder = $this->get('form.factory')->createBuilder(EventType::class, $event);
        $form = $formBuilder->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('wp_tournament_adminEvent');
        }

        return $this->render('WPTournamentBundle:Tournament:adminEventEdit.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function adminEventDelAction($id) {
        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository('WPTournamentBundle:Event')->find($id);

        $cover = $em->getRepository('WPTournamentBundle:Image')->find($event->getCover()->getId());
        $planning = $em->getRepository('WPTournamentBundle:Image')->find($event->getPlanning()->getId());

        $em->remove($event);

        $em->remove($cover);
        $em->remove($planning);

        $em->flush();

        return $this->redirectToRoute('wp_tournament_adminEvent');
    }

    public function adminUserAction() {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('WPUserBundle:User')->findAll();
        return $this->render('WPTournamentBundle:Tournament:adminUser.html.twig', array(
                    'users' => $users
        ));
    }

    public function adminUserBanAction($id) {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('WPUserBundle:User')->find($id);

        if ($user->isEnabled()) {
            $user->setEnabled(0);
        } else {
            $user->setEnabled(1);
        }


        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('wp_tournament_adminUser');
    }

    public function searchAction($page) {
        $em = $this->getDoctrine()->getManager();

        $events = null;
        $paramRoute = null;

        $nbPerPage = 1;

        if (isset($_GET['keyword'])) {
            $keywords = $_GET['keyword'];

            $ispro = $_GET['pro'];
            $paramRoute = ['keyword' => $keywords, 'pro' => $ispro];

            $events = $em->getRepository('WPTournamentBundle:Event')->getSearchEvent($page, $nbPerPage, $keywords, $ispro);
        }

        $nbPages = ceil(count($events) / $nbPerPage);

        if (count($events) != 0) {
            if ($page > $nbPages || $page < 1) {
                throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
            }
        }

        return $this->render('WPTournamentBundle:Tournament:search.html.twig', array(
                    'events' => $events,
                    'nbPages' => $nbPages,
                    'page' => $page,
                    'paramRoute' => $paramRoute
        ));
    }

    public function adminTeamAction() {
        $em = $this->getDoctrine()->getManager();

        $teams = $em->getRepository('WPTournamentBundle:Team')->findAll();
        return $this->render('WPTournamentBundle:Tournament:adminTeam.html.twig', array(
                    'teams' => $teams
        ));
    }

    public function adminTeamAddAction(Request $request) {
        $team = new Team();

        $formBuilder = $this->get('form.factory')->createBuilder(EventType::class, $team);
        $form = $formBuilder->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($team);
            $em->flush();

            return $this->redirectToRoute('wp_tournament_adminTeam');
        }

        return $this->render('WPTournamentBundle:Tournament:adminTeamAdd.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function adminEventTeamAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $team = $em->getRepository('WPTournamentBundle:Team')->find($id);

        $formBuilder = $this->get('form.factory')->createBuilder(TeamType::class, $team);
        $form = $formBuilder->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->persist($team);
            $em->flush();

            return $this->redirectToRoute('wp_tournament_adminTeam');
        }

        return $this->render('WPTournamentBundle:Tournament:adminTeamEdit.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function adminTeamDelAction($id) {
        $em = $this->getDoctrine()->getManager();

        $team = $em->getRepository('WPTournamentBundle:Team')->find($id);

        $em->remove($team);

        $em->flush();

        return $this->redirectToRoute('wp_tournament_adminTeam');
    }

    public function adminMessagesAction() {
        $em = $this->getDoctrine()->getManager();

        $msgRead = $em->getRepository('WPTournamentBundle:Form')->findBy(['isread' => 1]);
        $msgNotRead = $em->getRepository('WPTournamentBundle:Form')->findBy(['isread' => 0]);

        return $this->render('WPTournamentBundle:Tournament:adminMessages.html.twig', array(
                    'msgRead' => $msgRead,
                    'msgNotRead' => $msgNotRead
        ));
    }

    public function adminReadMessageAction($id) {
        $em = $this->getDoctrine()->getManager();

        $msg = $em->getRepository('WPTournamentBundle:Form')->find($id);

        $msg->setIsread(1);
        $em->persist($msg);
        $em->flush();

        return $this->render('WPTournamentBundle:Tournament:adminMessageRead.html.twig', array(
                    'msg' => $msg
        ));
    }

    public function adminDelMessageAction($id) {
        $em = $this->getDoctrine()->getManager();

        $msg = $em->getRepository('WPTournamentBundle:Form')->find($id);

        $em->remove($msg);
        $em->flush();

        return $this->redirectToRoute('wp_tournament_adminMessages');
    }

}
