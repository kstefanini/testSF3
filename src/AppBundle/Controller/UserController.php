<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use FOS\UserBundle\Controller\RegistrationController;
use FOS\UserBundle\Event\GetResponseUserEvent;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;

class UserController extends RegistrationController
{
    /**
     * @Route("/users/add", name="user_add")
     */
    public function addAction(Request $request, EntityManagerInterface $em)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        return $this->modify($user, $request, $em);
    }

    /**
     * @Route("/users/addUser", name="user_add2")
     */
    public function addUserAction(Request $request, EntityManagerInterface $em)
    {
        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                return $response;
            }

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('user/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/user/{userId}/delete", name="user_delete", requirements={"userId": "\d+"})
     */
    public function deleteAction($userId, Request $request, EntityManagerInterface $em)
    {
        $user = $em->getRepository('AppBundle:User')
            ->find($userId);

        if (!$user) {
            $this->addFlash(
                'error',
                'No User found for id '.$userId
            );
            return $this->redirectToRoute('admin');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash(
            'notice',
            'User deleted!'
        );

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/user/{userId}/edit", name="user_edit", requirements={"userId": "\d+"})
     */
    public function editAction($userId, Request $request, EntityManagerInterface $em)
    {
        $user = $em->getRepository('AppBundle:User')
            ->find($userId);

        if (!$user) {
            $this->addFlash(
                'error',
                'No User found for id '.$userId
            );
            return $this->redirectToRoute('admin');
        }

        return $this->modify($user, $request, $em);
    }

    private function modify(User $user, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $em->persist($task);
            $em->flush();

            $admin_url = $this->container->get('router')->generate('admin');

            $this->addFlash(
                'notice',
                'Your changes were saved! <a href="'.$admin_url.'">Go back to admin page</a>'
            );

            // return $this->redirectToRoute('admin');
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
