<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CourseBundle\Manager;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\SettingsBundle\Model\Settings;
use Sylius\Bundle\SettingsBundle\Model\SettingsInterface;
use Sylius\Bundle\SettingsBundle\Schema\SchemaRegistryInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilder;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ValidatorInterface;
use Chamilo\SettingsBundle\Manager\SettingsManager as ChamiloSettingsManager;
use Chamilo\CourseBundle\Entity\CCourseSetting;
use Chamilo\CoreBundle\Entity\Course;
use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SettingsManager
 * Course settings manager.
 * @package Chamilo\CourseBundle\Manager
 */
class SettingsManager extends ChamiloSettingsManager
{
    protected $course;

    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param Course $course
     */
    public function setCourse(Course $course)
    {
        $this->course = $course;
    }

    /**
     * {@inheritdoc}
     */
    public function load($schemaAlias, $namespace = null, $ignoreUnknown = true)
    {
        /*blog_management
        blog
        course_maintenance //maintenance
        course_setting settings*/


        $schemaAlias = 'chamilo_course.settings.'.$schemaAlias;

        /** @var SchemaInterface $schema */
        $schema = $this->schemaRegistry->get($schemaAlias);

        /** @var SettingsResolverInterface $resolver */
        $resolver = $this->resolverRegistry->get($schemaAlias);

        // try to resolve settings for schema alias and namespace
        $settings = $resolver->resolve($schemaAlias, $namespace);

        if (!$settings) {
            $settings = $this->settingsFactory->createNew();
            $settings->setSchemaAlias($schemaAlias);
        }

         // We need to get a plain parameters array since we use the options resolver on it
        $parameters = $settings->getParameters();

        $settingsBuilder = new SettingsBuilder();
        $schema->buildSettings($settingsBuilder);

        // Remove unknown settings' parameters (e.g. From a previous version of the settings schema)
        if (true === $ignoreUnknown) {
            foreach ($parameters as $name => $value) {
                if (!$settingsBuilder->isDefined($name)) {
                    unset($parameters[$name]);
                }
            }
        }

        $parameters = $settingsBuilder->resolve($parameters);
        $settings->setParameters($parameters);

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SettingsInterface $settings)
    {
        $namespace = $settings->getSchemaAlias();

        /** @var SchemaInterface $schema */
        $schema = $this->schemaRegistry->get($settings->getSchemaAlias());

        $settingsBuilder = new SettingsBuilder();
        $schema->buildSettings($settingsBuilder);

        $parameters = $settingsBuilder->resolve($settings->getParameters());
        $settings->setParameters($parameters);

        /*foreach ($settingsBuilder->getTransformers() as $parameter => $transformer) {
            if (array_key_exists($parameter, $parameters)) {
                $parameters[$parameter] = $transformer->transform($parameters[$parameter]);
            }
        }*/

        /*if (isset($this->resolvedSettings[$namespace])) {
            $transformedParameters = $this->transformParameters($settingsBuilder, $parameters);
            $this->resolvedSettings[$namespace]->setParameters($transformedParameters);
        }*/

        $repo = $this->manager->getRepository('ChamiloCoreBundle:SettingsCurrent');
        $persistedParameters = $repo->findBy(array('category' => $settings->getSchemaAlias()));
        $persistedParametersMap = array();

        foreach ($persistedParameters as $parameter) {
            $persistedParametersMap[$parameter->getTitle()] = $parameter;
        }

        /** @var SettingsEvent $event */
        /*$event = $this->eventDispatcher->dispatch(
            SettingsEvent::PRE_SAVE,
            new SettingsEvent($settings, $parameters)
        );*/

        /** @var \Chamilo\CoreBundle\Entity\SettingsCurrent $url */
        //$url = $event->getArgument('url');
        $url = $this->getUrl();

        $simpleCategoryName = str_replace('chamilo_course.settings.', '', $namespace);

        foreach ($parameters as $name => $value) {
            if (isset($persistedParametersMap[$name])) {
                $persistedParametersMap[$name]->setValue($value);
            } else {
                $parameter = new CCourseSetting();
                $parameter
                    ->setTitle($name)
                    ->setVariable($name)
                    ->setCategory($namespace)
                    ->setValue($value)
                    ->setCId($this->getCourse()->getId())
                ;

                $this->manager->persist($parameter);
            }
        }

        $this->manager->flush();

        return;

        $schema = $this->schemaRegistry->getSchema($namespace);

        $settingsBuilder = new SettingsBuilder();
        $schema->buildSettings($settingsBuilder);

        $parameters = $settingsBuilder->resolve($settings->getParameters());

        foreach ($settingsBuilder->getTransformers() as $parameter => $transformer) {
            if (array_key_exists($parameter, $parameters)) {
                $parameters[$parameter] = $transformer->transform($parameters[$parameter]);
            }
        }

        if (isset($this->resolvedSettings[$namespace])) {
            $this->resolvedSettings[$namespace]->setParameters($parameters);
        }

        $persistedParameters = $this->parameterRepository->findBy(
            array('category' => $namespace, 'cId' => $this->getCourse()->getId())
        );

        $persistedParametersMap = array();

        foreach ($persistedParameters as $parameter) {
            $persistedParametersMap[$parameter->getName()] = $parameter;
        }

        foreach ($parameters as $name => $value) {
            if (isset($persistedParametersMap[$name])) {
                $persistedParametersMap[$name]->setValue($value);
            } else {
                /** @var CCourseSetting $parameter */
                //$parameter = $this->parameterFactory->createNew();
                $parameter = new CCourseSetting();
                $parameter
                    ->setNamespace($namespace)
                    ->setName($name)
                    ->setValue($value)
                    ->setCId($this->getCourse()->getId())
                ;

                /* @var $errors ConstraintViolationListInterface */
                $errors = $this->validator->validate($parameter);
                if (0 < $errors->count()) {
                    throw new ValidatorException($errors->get(0)->getMessage());
                }

                $this->parameterManager->persist($parameter);
            }
        }

        $this->parameterManager->flush();

        $this->cache->save($namespace, $parameters);
    }

    /**
     * Load parameter from database.
     *
     * @param string $namespace
     *
     * @return array
     */
    private function getParameters($namespace)
    {
        $repo = $this->manager->getRepository('ChamiloCourseBundle:CCourseSetting');
        $parameters = [];
        foreach ($repo->findBy(array('category' => $namespace)) as $parameter) {
            $parameters[$parameter->getName()] = $parameter->getValue();
        }
    }
}
