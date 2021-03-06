<?php
/**
 * SidebarController.php
 * avanzu-admin
 * Date: 23.02.14
 */

namespace Chamilo\ThemeBundle\Controller;

use Chamilo\ThemeBundle\Event\ShowUserEvent;
use Chamilo\ThemeBundle\Event\SidebarMenuEvent;
use Chamilo\ThemeBundle\Event\SidebarMenuKnpEvent;
use Chamilo\ThemeBundle\Event\ThemeEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SidebarController
 * @package Chamilo\ThemeBundle\Controller
 */
class SidebarController extends Controller
{
    /**
     * "Hello user" section
     * @return Response
     */
    public function userPanelAction()
    {
        if (!$this->getDispatcher()->hasListeners(ThemeEvents::THEME_SIDEBAR_USER)) {
            return new Response();
        }

        $userEvent = $this->getDispatcher()->dispatch(ThemeEvents::THEME_SIDEBAR_USER, new ShowUserEvent());

        return $this->render(
            'ChamiloThemeBundle:Sidebar:user-panel.html.twig',
            array(
                'user' => $userEvent->getUser()
            )
        );
    }

     /**
     * User menu section
     * @return Response
     */
    public function userProfileAction()
    {
        if (!$this->getDispatcher()->hasListeners(ThemeEvents::THEME_SIDEBAR_USER)) {
            return new Response();
        }

        $userEvent = $this->getDispatcher()->dispatch(ThemeEvents::THEME_SIDEBAR_USER, new ShowUserEvent());

        return $this->render(
            'ChamiloThemeBundle:Sidebar:user-profile.html.twig',
            array(
                'user' => $userEvent->getUser()
            )
        );
    }

      /**
     * User menu section
     * @return Response
     */
    public function socialPanelAction()
    {
        if (!$this->getDispatcher()->hasListeners(ThemeEvents::THEME_SIDEBAR_USER)) {
            return new Response();
        }

        $userEvent = $this->getDispatcher()->dispatch(ThemeEvents::THEME_SIDEBAR_USER, new ShowUserEvent());

        return $this->render(
            'ChamiloThemeBundle:Sidebar:social-panel.html.twig',
            array(
                'user' => $userEvent->getUser()
            )
        );
    }

    /**
     * Search bar
     * @return Response
     */
    public function searchFormAction()
    {
        return $this->render('ChamiloThemeBundle:Sidebar:search-form.html.twig', array());
    }

    /**
     * @return EventDispatcher
     */
    protected function getDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function menuAction(Request $request)
    {
        if (!$this->getDispatcher()->hasListeners(ThemeEvents::THEME_SIDEBAR_SETUP_MENU)) {
            return new Response();
        }

        $event = $this->getDispatcher()->dispatch(
            ThemeEvents::THEME_SIDEBAR_SETUP_MENU,
            new SidebarMenuEvent($request)
        );

        return $this->render(
            'ChamiloThemeBundle:Sidebar:menu.html.twig',
            array(
                'menu' => $event->getItems()
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function menuKnpAction(Request $request)
    {
        if (!$this->getDispatcher()->hasListeners(ThemeEvents::THEME_SIDEBAR_SETUP_MENU_KNP)) {
            return new Response();
        }

        /** @var SidebarMenuKnpEvent $event */
        $event = $this->getDispatcher()->dispatch(
            ThemeEvents::THEME_SIDEBAR_SETUP_MENU_KNP,
            new SidebarMenuKnpEvent($request)
        );

        return $this->render(
            'ChamiloThemeBundle:Sidebar:menu_knp.html.twig',
            array(
                'menu' => $event->getMenu()
            )
        );
    }
}
