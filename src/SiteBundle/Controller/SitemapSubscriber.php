<?php

namespace SiteBundle\Controller;

use FOS\UserBundle\Propel\UserQuery;
use SiteBundle\Model\ObjectsQuery;
use SiteBundle\Model\ObjectTypesQuery;
use SiteBundle\Model\PagesQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event)
    {
        $this->registerBlogPostsUrls($event->getUrlContainer());
    }

    /**
     * @param UrlContainerInterface $urls
     */
    public function registerBlogPostsUrls(UrlContainerInterface $urls)
    {
        $pages = PagesQuery::create()->find();
        foreach ($pages as $page) {
            $urls->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate(
                        'site_default_page',
                        array('alias' => $page->getAlias()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'default'
            );
        }

        $agents = UserQuery::create()->filterByRole('ROLE_AGENT')->find();
        foreach ($agents as $agent) {
            $urls->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate(
                        'site_agent_agent',
                        array('id' => $agent->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'agents'
            );
        }

        $categories = ObjectTypesQuery::create()->find();
        foreach ($categories as $category) {
            $urls->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate(
                        'site_catalog_alias',
                        array('alias' => $category->getAlias()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'catalog'
            );
        }

        $objects = ObjectsQuery::create()->find();
        foreach ($objects as $object) {
            $urls->addUrl(
                new UrlConcrete(
                    $this->urlGenerator->generate(
                        'site_catalog_object',
                        array('id' => $object->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'catalog'
            );
        }
    }
}