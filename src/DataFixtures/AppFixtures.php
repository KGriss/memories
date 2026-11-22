<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Memory;
use App\Entity\Mind;
use App\Entity\Travel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    // defines reference names for instances of Mind
    private const LUCAS_MIND = 'lucas-mind';
    private const ALICE_MIND = 'alice-mind';

    // defines reference names for instances of Memory
    private const PARIS_MEMORY = 'paris-memory';
    private const NICE_MEMORY = 'nice-memory';
    private const ROME_MEMORY = 'rome-memory';
    private const LONDON_MEMORY = 'london-memory';
    private const ALPS_MEMORY = 'alps-memory';
    private const LISBON_MEMORY = 'lisbon-memory';

    /**
     * Resources directory for mock upload files prepared in advance, for use with
     * prepareResourceImageUploadedFile()
     *
     * @var string
     */
    private const EXAMPLE_IMAGES_RESOURCES_DIR = __DIR__."/../../resources/images/";

    /**
     * Temporary filename saved for later cleanup necessary for prepareResourceImageUploadedFile()
     *
     * @var string
     */
    private ?string $tempfilename = null;

    private Filesystem $filesystem;

    public function __construct()
    {
        // needed by prepareResourceImageUploadedFile()
        $this->filesystem = new Filesystem();
    }

    /**
     * Generates initialization data for minds : [member reference, description, mind reference]
     *
     * @return \Generator
     */
    private static function mindsGenerator()
    {
        yield [UserFixtures::LUCAS, 'Souvenirs de voyage de Lucas', self::LUCAS_MIND];
        yield [UserFixtures::ALICE, 'Carnet de voyages d\'Alice', self::ALICE_MIND];
    }

    /**
     * Generates initialization data for memories :
     *  [mind reference, name, description, location, image file name, memory reference]
     *
     * @return \Generator
     */
    private static function memoriesGenerator()
    {
        yield [self::LUCAS_MIND, 'Week-end à Paris', 'Promenade dans Paris et visite des principaux monuments.', 'Paris, France', 'paris.png', self::PARIS_MEMORY];
        yield [self::LUCAS_MIND, 'Vacances à Nice', 'Quelques jours sur la Côte d\'Azur.', 'Nice, France', 'nice.png', self::NICE_MEMORY];
        yield [self::LUCAS_MIND, 'Découverte de Rome', 'Visite du Colisée et du centre historique.', 'Rome, Italie', 'rome.png', self::ROME_MEMORY];
        yield [self::ALICE_MIND, 'Séjour à Londres', 'Découverte de Westminster et des musées.', 'Londres, Royaume-Uni', 'londres.png', self::LONDON_MEMORY];
        yield [self::ALICE_MIND, 'Randonnée dans les Alpes', 'Randonnée avec plusieurs étapes en altitude.', 'Alpes, France', 'alpes.png', self::ALPS_MEMORY];
        yield [self::ALICE_MIND, 'Voyage à Lisbonne', 'Découverte des quartiers historiques.', 'Lisbonne, Portugal', 'lisbonne.png', self::LISBON_MEMORY];
    }

    /**
     * Generates initialization data for travels :
     *  [creator reference, name, description, published, memory references]
     *
     * @return \Generator
     */
    private static function travelsGenerator()
    {
        yield [UserFixtures::LUCAS, 'Escapades en France', 'Petits séjours en France, entre Paris et la Méditerranée.', true, [self::PARIS_MEMORY, self::NICE_MEMORY]];
        yield [UserFixtures::LUCAS, 'Capitales européennes', 'Les grandes villes visitées en Europe.', false, [self::PARIS_MEMORY, self::ROME_MEMORY]];
        yield [UserFixtures::ALICE, 'Villes d\'Europe', 'Londres et Lisbonne, deux séjours en ville.', true, [self::LONDON_MEMORY, self::LISBON_MEMORY]];
        yield [UserFixtures::ALICE, 'Montagne', 'Randonnées et grand air.', false, [self::ALPS_MEMORY]];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    /**
     * Prepare an UploadedFile to pass to VichUploader
     *
     * This will prepare a mock upload file for calls to VichUploader setImageFile() setter
     * working on a copy of a resource file prepared in the project dir under
     * class::EXAMPLE_IMAGES_RESOURCES_DIR
     *
     * @param string $imagefilename
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     *
     * after completion, one needs to call cleanupResourceImageUploadedFile()
     *
     * The principle (borrowed from https://github.com/dustin10/VichUploaderBundle/issues/70#issuecomment-307373831 )
     * is to prepare an UploadedFile, but when saving processing occurs, in VichUploader machinery, the file would be removed.
     * To preserve subsequent calls to fixture:load, we work on a copy of the resource file.
     */
    private function prepareResourceImageUploadedFile($imagefilename) {
        // duplicate the file, so that the "upload" won't remove it
        $src =  Path::join(self::EXAMPLE_IMAGES_RESOURCES_DIR, $imagefilename);
        $ext = pathinfo($imagefilename, PATHINFO_EXTENSION);
        $this->tempfilename =$this->filesystem->tempnam(sys_get_temp_dir(), 'fixture-upload-', '.'.$ext);
        $this->filesystem->copy($src, $this->tempfilename, true);
        $file = new UploadedFile(
            $this->tempfilename,
            $imagefilename,
            mime_content_type($this->tempfilename),
            null,
            true //  Set test mode true !!! " Local files are used in test mode hence the code should not enforce HTTP uploads."
            );
        return $file;
    }

    /**
     * Cleanup temporary files that may have been created by prepareResourceImageUploadedFile()
     *
     * The temp file created by prepareResourceImageUploadedFile() is saved in self::tempfilename
     */
    private function cleanupResourceImageUploadedFile() {
        if ($this->tempfilename) {
            try {
                $this->filesystem->remove($this->tempfilename);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
            $this->tempfilename = null;
        }
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::mindsGenerator() as [$memberReference, $description, $mindReference]) {
            $member = $this->getReference($memberReference, Member::class);
            $mind = new Mind();
            $mind->setDescription($description);
            $member->setMind($mind);
            $manager->persist($mind);
            $manager->flush();
            // Once the Mind instance has been saved to DB
            // it has a valid Id generated by Doctrine, and can thus
            // be saved as a future reference
            $this->addReference($mindReference, $mind);
        }

        foreach (self::memoriesGenerator() as [$mindReference, $name, $description, $location, $imagefilename, $memoryReference]) {
            // Retrieve the One-side instance of Mind from its reference name
            $mind = $this->getReference($mindReference, Mind::class);
            $memory = new Memory();
            $memory->setName($name);
            $memory->setDescription($description);
            $memory->setLocation($location);
            if($imagefilename) {
                // prepare the mock uploaded file for VichUploader machinery
                $file = $this->prepareResourceImageUploadedFile($imagefilename);
                $memory->setImageFile($file);
            }
            // Add the Many-side Memory to its containing mind
            $mind->addMemory($memory);
            $manager->persist($memory);
            $manager->flush();
            $this->addReference($memoryReference, $memory);
            if($imagefilename) {
                // cleanup the mock uploaded file temp files
                $this->cleanupResourceImageUploadedFile();
            }
        }

        foreach (self::travelsGenerator() as [$memberReference, $name, $description, $published, $memoryReferences]) {
            // Retrieve the creator of the travel from its reference name
            $member = $this->getReference($memberReference, Member::class);
            $travel = new Travel();
            $travel->setCreator($member);
            $travel->setName($name);
            $travel->setDescription($description);
            $travel->setPublished($published);
            foreach ($memoryReferences as $memoryReference) {
                // Retrieve the Memory instance from its reference name
                $memory = $this->getReference($memoryReference, Memory::class);
                $travel->addMemory($memory);
            }
            $manager->persist($travel);
        }
        $manager->flush();
    }
}
