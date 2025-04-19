<?php

namespace Drupal\visitors_geoip\Commands;

use Drupal\Core\Archiver\Tar;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Defines a Drush command related to MaxMind.
 */
class MaxMindCommands extends DrushCommands {

  const URL = 'https://download.maxmind.com/app/geoip_download';

  /**
   * The http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The geo ip settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The visitors rebuild location service.
   *
   * @var \Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface
   */
  protected $location;

  /**
   * Drush commands for rebuilding logs.
   *
   * @param \GuzzleHttp\Client $http_client
   *   The visitors rebuild route service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The state service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The visitors rebuild ip address service.
   * @param \Drupal\visitors_geoip\VisitorsGeoIpRebuildLocationInterface $location
   *   The visitors rebuild location service.
   */
  public function __construct(
    Client $http_client,
    ConfigFactoryInterface $config_factory,
    FileSystemInterface $file_system,
    VisitorsGeoIpRebuildLocationInterface $location,
  ) {
    parent::__construct();

    $this->client = $http_client;
    $this->settings = $config_factory->get('visitors_geoip.settings');
    $this->fileSystem = $file_system;
    $this->location = $location;
  }

  /**
   * Regenerates routes from path.
   *
   * @command visitors:download:city
   * @aliases visitors-download-city
   *
   * @usage drush visitors:download:city
   *  Generates routes from the visitors_path.
   */
  public function downloadCities() {
    $license = $this->settings->get('license');
    if (empty($license)) {
      $this->output()->writeln('You must set a MaxMind license key in the visitors_geoip settings.');
      return;
    }

    $temp_file = $this->fileSystem->tempnam('temporary://', 'geolite2_city_');
    $real_file = $this->fileSystem->realpath($temp_file);

    $geoip_path = $this->settings->get('geoip_path');
    $path = $this->fileSystem->dirname($geoip_path);
    $path = $this->fileSystem->realpath($path);
    $temp_path = $this->fileSystem->realpath('temporary://');

    // Get the Symfony Console output interface.
    $output = $this->output();
    // $output->writeLn("There are $total ip addresses to process.");
    $progressBar = new ProgressBar($output);
    $progressBar->setFormat('debug');
    $progressBar->start();
    // Initiate the request to download the file.
    $this->client->get(
      self::URL,
      [
        'query' => [
          'edition_id' => 'GeoLite2-City',
          'license_key' => $this->settings->get('license'),
          'suffix' => 'tar.gz',
        ],
        'sink' => $real_file,
        'progress' => function ($curl, $downloadTotal, $downloadedBytes) use ($progressBar) {
          $progressBar->setMaxSteps($downloadTotal);
          $progressBar->setProgress($downloadedBytes);
        },
      ]);

    // Finish the progress bar.
    $progressBar->finish();
    // Add a new line after the progress bar.
    $output->writeln('');

    $tar = new Tar($real_file, ['compress' => 'gz']);
    $content = $tar->listContents();
    $matches = preg_grep('/\.mmdb$/', $content);
    $database = reset($matches);

    $tar->extract($temp_path, [$database]);
    $db = end(explode('/', $database));
    if (version_compare(\Drupal::VERSION, '10.3.0', '>=')) {
      $this->fileSystem->copy($temp_path . '/' . $database, $path . '/' . $db, FileExists::Replace);
    }
    else {
      // @phpstan-ignore-next-line
      $this->fileSystem->copy($temp_path . '/' . $database, $path . '/' . $db, FileSystemInterface::EXISTS_REPLACE);
    }

    // Output a completion message.
    $output->writeln('Download completed!');
  }

  /**
   * Regenerates location from ip address.
   *
   * @command visitors:rebuild:location
   * @aliases visitors-rebuild-location
   *
   * @usage drush visitors:rebuild:location
   *  Generates location from the visitors_ip.
   */
  public function locations() {

    $records = $this->location->getLocations();
    $total = count($records);

    // Get the Symfony Console output interface.
    $output = $this->output();
    $output->writeLn("There are $total locations to process.");
    $progressBar = new ProgressBar($output, $total);
    $progressBar->setFormat('debug');
    $progressBar->start();

    do {
      $progressBar->advance();
      $record = array_pop($records);
      if (empty($record)) {
        continue;
      }

      $this->location->rebuild($record);

    } while (count($records));

    // Finish the progress bar.
    $progressBar->finish();
    // Add a new line after the progress bar.
    $output->writeln('');

    // Output a completion message.
    $output->writeln('Task completed!');
  }

}
