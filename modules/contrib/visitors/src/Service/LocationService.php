<?php

namespace Drupal\visitors\Service;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\visitors\VisitorsLocationInterface;

/**
 * The location service.
 */
class LocationService implements VisitorsLocationInterface {
  use StringTranslationTrait;

  /**
   * List of countries.
   *
   * @var array
   */
  protected $countries = [];

  /**
   * List of continents.
   *
   * @var array
   */
  protected $continents = [];

  /**
   * Constructs a new LocationService object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(TranslationInterface $string_translation) {

    $this->setStringTranslation($string_translation);

    $this->continents = [
      'AF' => $this->t('Africa'),
      'AS' => $this->t('Asia'),
      'EU' => $this->t('Europe'),
      'NA' => $this->t('North America'),
      'OC' => $this->t('Oceania'),
      'SA' => $this->t('South America'),
      'AQ' => $this->t('Antarctica'),
    ];

    $this->countries = [
      'AF' => [
        'label' => $this->t('Afghanistan'),
        'continent' => 'AS',
      ],
      'AX' => [
        'label' => $this->t('Aland Islands'),
        'continent' => 'EU',
      ],
      'AL' => [
        'label' => $this->t('Albania'),
        'continent' => 'EU',
      ],
      'DZ' => [
        'label' => $this->t('Algeria'),
        'continent' => 'AF',
      ],
      'AS' => [
        'label' => $this->t('American Samoa'),
        'continent' => 'OC',
      ],
      'AD' => [
        'label' => $this->t('Andorra'),
        'continent' => 'EU',
      ],
      'AO' => [
        'label' => $this->t('Angola'),
        'continent' => 'AF',
      ],
      'AI' => [
        'label' => $this->t('Anguilla'),
        'continent' => 'NA',
      ],
      'AQ' => [
        'label' => $this->t('Antarctica'),
        'continent' => 'AQ',
      ],
      'AG' => [
        'label' => $this->t('Antigua and Barbuda'),
        'continent' => 'NA',
      ],
      'AR' => [
        'label' => $this->t('Argentina'),
        'continent' => 'SA',
      ],
      'AM' => [
        'label' => $this->t('Armenia'),
        'continent' => 'AS',
      ],
      'AW' => [
        'label' => $this->t('Aruba'),
        'continent' => 'SA',
      ],
      'AU' => [
        'label' => $this->t('Australia'),
        'continent' => 'OC',
      ],
      'AT' => [
        'label' => $this->t('Austria'),
        'continent' => 'EU',
      ],
      'AZ' => [
        'label' => $this->t('Azerbaijan'),
        'continent' => 'AS',
      ],
      'BS' => [
        'label' => $this->t('Bahamas'),
        'continent' => 'NA',
      ],
      'BH' => [
        'label' => $this->t('Bahrain'),
        'continent' => 'AS',
      ],
      'BD' => [
        'label' => $this->t('Bangladesh'),
        'continent' => 'AS',
      ],
      'BB' => [
        'label' => $this->t('Barbados'),
        'continent' => 'SA',
      ],
      'BY' => [
        'label' => $this->t('Belarus'),
        'continent' => 'EU',
      ],
      'BE' => [
        'label' => $this->t('Belgium'),
        'continent' => 'EU',
      ],
      'BZ' => [
        'label' => $this->t('Belize'),
        'continent' => 'NA"',
      ],
      'BJ' => [
        'label' => $this->t('Benin'),
        'continent' => 'AF',
      ],
      'BM' => [
        'label' => $this->t('Bermuda'),
        'continent' => 'NA',
      ],
      'BT' => [
        'label' => $this->t('Bhutan'),
        'continent' => 'AS',
      ],
      'BO' => [
        'label' => $this->t('Bolivia'),
        'continent' => 'SA',
      ],
      'BQ' => [
        'label' => $this->t('Bonaire, Sint Eustatius and Saba'),
        'continent' => 'SA',
      ],
      'BA' => [
        'label' => $this->t('Bosnia and Herzegovina'),
        'continent' => 'EU',
      ],
      'BW' => [
        'label' => $this->t('Botswana'),
        'continent' => 'AF',
      ],
      'BV' => [
        'label' => $this->t('Bouvet Island'),
        'continent' => 'SA',
      ],
      'BR' => [
        'label' => $this->t('Brazil'),
        'continent' => 'SA',
      ],
      'IO' => [
        'label' => $this->t('British Indian Ocean Territory'),
        'continent' => 'AF',
      ],
      'BN' => [
        'label' => $this->t('Brunei Darussalam'),
        'continent' => 'AS',
      ],
      'BG' => [
        'label' => $this->t('Bulgaria'),
        'continent' => 'EU',
      ],
      'BF' => [
        'label' => $this->t('Burkina Faso'),
        'continent' => 'AF',
      ],
      'BI' => [
        'label' => $this->t('Burundi'),
        'continent' => 'AF',
      ],
      'CV' => [
        'label' => $this->t('Cabo Verde'),
        'continent' => 'AF',
      ],
      'KH' => [
        'label' => $this->t('Cambodia'),
        'continent' => 'AS',
      ],
      'CM' => [
        'label' => $this->t('Cameroon'),
        'continent' => 'AF',
      ],
      'CA' => [
        'label' => $this->t('Canada'),
        'continent' => 'NA',
      ],
      'KY' => [
        'label' => $this->t('Cayman Islands'),
        'continent' => 'NA',
      ],
      'CF' => [
        'label' => $this->t('Central African Republic'),
        'continent' => 'AF',
      ],
      'TD' => [
        'label' => $this->t('Chad'),
        'continent' => 'AF',
      ],
      'CL' => [
        'label' => $this->t('Chile'),
        'continent' => 'SA',
      ],
      'CN' => [
        'label' => $this->t('China'),
        'continent' => 'AS',
      ],
      'CX' => [
        'label' => $this->t('Christmas Island'),
        'continent' => 'OC',
      ],
      'CC' => [
        'label' => $this->t('Cocos (Keeling) Islands'),
        'continent' => 'OC',
      ],
      'CO' => [
        'label' => $this->t('Colombia'),
        'continent' => 'SA',
      ],
      'KM' => [
        'label' => $this->t('Comoros'),
        'continent' => 'AF',
      ],
      'CG' => [
        'label' => $this->t('Congo'),
        'continent' => 'AF',
      ],
      'CD' => [
        'label' => $this->t('Congo, Democratic Republic of the'),
        'continent' => 'AF',
      ],
      'CK' => [
        'label' => $this->t('Cook Islands'),
        'continent' => 'OC',
      ],
      'CR' => [
        'label' => $this->t('Costa Rica'),
        'continent' => 'NA',
      ],
      'CI' => [
        'label' => $this->t("Côte d'Ivoire"),
        'continent' => 'AF',
      ],
      'HR' => [
        'label' => $this->t('Croatia'),
        'continent' => 'EU',
      ],
      'CU' => [
        'label' => $this->t('Cuba'),
        'continent' => 'NA',
      ],
      'CW' => [
        'label' => $this->t('Curaçao'),
        'continent' => 'SA',
      ],
      'CY' => [
        'label' => $this->t('Cyprus'),
        'continent' => 'AS',
      ],
      'CZ' => [
        'label' => $this->t('Czechia'),
        'continent' => 'EU',
      ],
      'DK' => [
        'label' => $this->t('Denmark'),
        'continent' => 'EU',
      ],
      'DJ' => [
        'label' => $this->t('Djibouti'),
        'continent' => 'AF',
      ],
      'DM' => [
        'label' => $this->t('Dominica'),
        'continent' => 'SA',
      ],
      'DO' => [
        'label' => $this->t('Dominican Republic'),
        'continent' => 'NA',
      ],
      'EC' => [
        'label' => $this->t('Ecuador'),
        'continent' => 'SA',
      ],
      'EG' => [
        'label' => $this->t('Egypt'),
        'continent' => 'AF',
      ],
      'SV' => [
        'label' => $this->t('El Salvador'),
        'continent' => 'SA',
      ],
      'GQ' => [
        'label' => $this->t('Equatorial Guinea'),
        'continent' => 'AF',
      ],
      'ER' => [
        'label' => $this->t('Eritrea'),
        'continent' => 'AF',
      ],
      'EE' => [
        'label' => $this->t('Estonia'),
        'continent' => 'EU',
      ],
      'SZ' => [
        'label' => $this->t('Eswatini'),
        'continent' => 'AF',
      ],
      'ET' => [
        'label' => $this->t('Ethiopia'),
        'continent' => 'AF',
      ],
      'FK' => [
        'label' => $this->t('Falkland Islands (Malvinas)'),
        'continent' => 'SA',
      ],
      'FO' => [
        'label' => $this->t('Faroe Islands'),
        'continent' => 'EU',
      ],
      'FJ' => [
        'label' => $this->t('Fiji'),
        'continent' => 'OC',
      ],
      'FI' => [
        'label' => $this->t('Finland'),
        'continent' => 'EU',
      ],
      'FR' => [
        'label' => $this->t('France'),
        'continent' => 'EU',
      ],
      'GF' => [
        'label' => $this->t('French Guiana'),
        'continent' => 'SA',
      ],
      'PF' => [
        'label' => $this->t('French Polynesia'),
        'continent' => 'OC',
      ],
      'TF' => [
        'label' => $this->t('French Southern Territories'),
        'continent' => 'AF',
      ],
      'GA' => [
        'label' => $this->t('Gabon'),
        'continent' => 'AF',
      ],
      'GM' => [
        'label' => $this->t('Gambia'),
        'continent' => 'AF',
      ],
      'GE' => [
        'label' => $this->t('Georgia'),
        'continent' => 'AS',
      ],
      'DE' => [
        'label' => $this->t('Germany'),
        'continent' => 'EU',
      ],
      'GH' => [
        'label' => $this->t('Ghana'),
        'continent' => 'AF',
      ],
      'GI' => [
        'label' => $this->t('Gibraltar'),
        'continent' => 'EU',
      ],
      'GR' => [
        'label' => $this->t('Greece'),
        'continent' => 'EU',
      ],
      'GL' => [
        'label' => $this->t('Greenland'),
        'continent' => 'NA',
      ],
      'GD' => [
        'label' => $this->t('Grenada'),
        'continent' => 'SA',
      ],
      'GP' => [
        'label' => $this->t('Guadeloupe'),
        'continent' => 'SA',
      ],
      'GU' => [
        'label' => $this->t('Guam'),
        'continent' => 'OC',
      ],
      'GT' => [
        'label' => $this->t('Guatemala'),
        'continent' => 'NA',
      ],
      'GG' => [
        'label' => $this->t('Guernsey'),
        'continent' => 'EU',
      ],
      'GN' => [
        'label' => $this->t('Guinea'),
        'continent' => 'AF',
      ],
      'GW' => [
        'label' => $this->t('Guinea-Bissau'),
        'continent' => 'AF',
      ],
      'GY' => [
        'label' => $this->t('Guyana'),
        'continent' => 'SA',
      ],
      'HT' => [
        'label' => $this->t('Haiti'),
        'continent' => 'NA',
      ],
      'HM' => [
        'label' => $this->t('Heard Island and McDonald Islands'),
        'continent' => 'OC',
      ],
      'VA' => [
        'label' => $this->t('Holy See'),
        'continent' => 'EU',
      ],
      'HN' => [
        'label' => $this->t('Honduras'),
        'continent' => 'NA',
      ],
      'HK' => [
        'label' => $this->t('Hong Kong'),
        'continent' => 'AS',
      ],
      'HU' => [
        'label' => $this->t('Hungary'),
        'continent' => 'EU',
      ],
      'IS' => [
        'label' => $this->t('Iceland'),
        'continent' => 'EU',
      ],
      'IN' => [
        'label' => $this->t('India'),
        'continent' => 'AS',
      ],
      'ID' => [
        'label' => $this->t('Indonesia'),
        'continent' => 'AS',
      ],
      'IR' => [
        'label' => $this->t('Iran'),
        'continent' => 'AS',
      ],
      'IQ' => [
        'label' => $this->t('Iraq'),
        'continent' => 'AS',
      ],
      'IE' => [
        'label' => $this->t('Ireland'),
        'continent' => 'EU',
      ],
      'IM' => [
        'label' => $this->t('Isle of Man'),
        'continent' => 'EU',
      ],
      'IL' => [
        'label' => $this->t('Israel'),
        'continent' => 'AS',
      ],
      'IT' => [
        'label' => $this->t('Italy'),
        'continent' => 'EU',
      ],
      'JM' => [
        'label' => $this->t('Jamaica'),
        'continent' => 'NA',
      ],
      'JP' => [
        'label' => $this->t('Japan'),
        'continent' => 'AS',
      ],
      'JE' => [
        'label' => $this->t('Jersey'),
        'continent' => 'EU',
      ],
      'JO' => [
        'label' => $this->t('Jordan'),
        'continent' => 'AS',
      ],
      'KZ' => [
        'label' => $this->t('Kazakhstan'),
        'continent' => 'AS',
      ],
      'KE' => [
        'label' => $this->t('Kenya,KE,AF'),
        'continent' => 'AF',
      ],
      'KI' => [
        'label' => $this->t('Kiribati'),
        'continent' => 'OC',
      ],
      'KP' => [
        'label' => $this->t("Korea (Democratic People's Republic of)"),
        'continent' => 'AS',
      ],
      'KR' => [
        'label' => $this->t('Korea, Republic of'),
        'continent' => 'AS',
      ],
      'KW' => [
        'label' => $this->t('Kuwait'),
        'continent' => 'AS',
      ],
      'KG' => [
        'label' => $this->t('Kyrgyzstan'),
        'continent' => 'AS',
      ],
      'LA' => [
        'label' => $this->t("Lao People's Democratic Republic"),
        'continent' => 'SA',
      ],
      'LV' => [
        'label' => $this->t('Latvia'),
        'continent' => 'EU',
      ],
      'LB' => [
        'label' => $this->t('Lebanon'),
        'continent' => 'AS',
      ],
      'LS' => [
        'label' => $this->t('Lesotho'),
        'continent' => 'AF',
      ],
      'LR' => [
        'label' => $this->t('Liberia'),
        'continent' => 'AF',
      ],
      'LY' => [
        'label' => $this->t('Libya'),
        'continent' => 'AF',
      ],
      'LI' => [
        'label' => $this->t('Liechtenstein'),
        'continent' => 'EU',
      ],
      'LT' => [
        'label' => $this->t('Lithuania'),
        'continent' => 'EU',
      ],
      'LU' => [
        'label' => $this->t('Luxembourg'),
        'continent' => 'EU',
      ],
      'MO' => [
        'label' => $this->t('Macao'),
        'continent' => 'AS',
      ],
      'MG' => [
        'label' => $this->t('Madagascar'),
        'continent' => 'AF',
      ],
      'MW' => [
        'label' => $this->t('Malawi'),
        'continent' => 'AF',
      ],
      'MY' => [
        'label' => $this->t('Malaysia'),
        'continent' => 'AS',
      ],
      'MV' => [
        'label' => $this->t('Maldives'),
        'continent' => 'AS',
      ],
      'ML' => [
        'label' => $this->t('Mali'),
        'continent' => 'AF',
      ],
      'MT' => [
        'label' => $this->t('Malta'),
        'continent' => 'EU',
      ],
      'MH' => [
        'label' => $this->t('Marshall Islands'),
        'continent' => 'OC',
      ],
      'MQ' => [
        'label' => $this->t('Martinique'),
        'continent' => 'SA',
      ],
      'MR' => [
        'label' => $this->t('Mauritania'),
        'continent' => 'AF',
      ],
      'MU' => [
        'label' => $this->t('Mauritius'),
        'continent' => 'AF',
      ],
      'YT' => [
        'label' => $this->t('Mayotte'),
        'continent' => 'AF',
      ],
      'MX' => [
        'label' => $this->t('Mexico'),
        'continent' => 'NA',
      ],
      'FM' => [
        'label' => $this->t('Micronesia (Federated States of)'),
        'continent' => 'OC',
      ],
      'MD' => [
        'label' => $this->t('Moldova, Republic of'),
        'continent' => 'EU',
      ],
      'MC' => [
        'label' => $this->t('Monaco'),
        'continent' => 'EU',
      ],
      'MN' => [
        'label' => $this->t('Mongolia'),
        'continent' => 'AS',
      ],
      'ME' => [
        'label' => $this->t('Montenegro'),
        'continent' => 'EU',
      ],
      'MS' => [
        'label' => $this->t('Montserrat'),
        'continent' => 'SA',
      ],
      'MA' => [
        'label' => $this->t('Morocco'),
        'continent' => 'AF',
      ],
      'MZ' => [
        'label' => $this->t('Mozambique'),
        'continent' => 'AF',
      ],
      'MM' => [
        'label' => $this->t('Myanmar'),
        'continent' => 'AS',
      ],
      'NA' => [
        'label' => $this->t('Namibia'),
        'continent' => 'AF',
      ],
      'NR' => [
        'label' => $this->t('Nauru'),
        'continent' => 'OC',
      ],
      'NP' => [
        'label' => $this->t('Nepal'),
        'continent' => 'AS',
      ],
      'NL' => [
        'label' => $this->t('Netherlands'),
        'continent' => 'EU',
      ],
      'NC' => [
        'label' => $this->t('New Caledonia'),
        'continent' => 'OC',
      ],
      'NZ' => [
        'label' => $this->t('New Zealand'),
        'continent' => 'OC',
      ],
      'NI' => [
        'label' => $this->t('Nicaragua'),
        'continent' => 'NA',
      ],
      'NE' => [
        'label' => $this->t('Niger'),
        'continent' => 'AF',
      ],
      'NG' => [
        'label' => $this->t('Nigeria'),
        'continent' => 'AF',
      ],
      'NU' => [
        'label' => $this->t('Niue'),
        'continent' => 'OC',
      ],
      'NF' => [
        'label' => $this->t('Norfolk Island'),
        'continent' => 'OC',
      ],
      'MK' => [
        'label' => $this->t('North Macedonia'),
        'continent' => 'EU',
      ],
      'MP' => [
        'label' => $this->t('Northern Mariana Islands'),
        'continent' => 'OC',
      ],
      'NO' => [
        'label' => $this->t('Norway'),
        'continent' => 'EU',
      ],
      'OM' => [
        'label' => $this->t('Oman'),
        'continent' => 'AS',
      ],
      'PK' => [
        'label' => $this->t('Pakistan'),
        'continent' => 'AS',
      ],
      'PW' => [
        'label' => $this->t('Palau'),
        'continent' => 'OC',
      ],
      'PS' => [
        'label' => $this->t('Palestine'),
        'continent' => 'AS',
      ],
      'PA' => [
        'label' => $this->t('Panama'),
        'continent' => 'NA',
      ],
      'PG' => [
        'label' => $this->t('Papua New Guinea'),
        'continent' => 'OC',
      ],
      'PY' => [
        'label' => $this->t('Paraguay'),
        'continent' => 'SA',
      ],
      'PE' => [
        'label' => $this->t('Peru'),
        'continent' => 'SA',
      ],
      'PH' => [
        'label' => $this->t('Philippines'),
        'continent' => 'AS',
      ],
      'PN' => [
        'label' => $this->t('Pitcairn'),
        'continent' => 'OC',
      ],
      'PL' => [
        'label' => $this->t('Poland'),
        'continent' => 'EU',
      ],
      'PT' => [
        'label' => $this->t('Portugal'),
        'continent' => 'EU',
      ],
      'PR' => [
        'label' => $this->t('Puerto Rico'),
        'continent' => 'NA',
      ],
      'QA' => [
        'label' => $this->t('Qatar'),
        'continent' => 'AS',
      ],
      'RE' => [
        'label' => $this->t('Réunion'),
        'continent' => 'AF',
      ],
      'RO' => [
        'label' => $this->t('Romania'),
        'continent' => 'EU',
      ],
      'RU' => [
        'label' => $this->t('Russia'),
        'continent' => 'EU',
      ],
      'RW' => [
        'label' => $this->t('Rwanda'),
        'continent' => 'AF',
      ],
      'BL' => [
        'label' => $this->t('Saint Barthélemy'),
        'continent' => 'SA',
      ],
      'SH' => [
        'label' => $this->t('Saint Helena, Ascension and Tristan da Cunha'),
        'continent' => 'AF',
      ],
      'KN' => [
        'label' => $this->t('Saint Kitts and Nevis'),
        'continent' => 'SA',
      ],
      'LC' => [
        'label' => $this->t('Saint Lucia'),
        'continent' => 'SA',
      ],
      'MF' => [
        'label' => $this->t('Saint Martin (French part)'),
        'continent' => 'SA',
      ],
      'PM' => [
        'label' => $this->t('Saint Pierre and Miquelon'),
        'continent' => 'NA',
      ],
      'VC' => [
        'label' => $this->t('Saint Vincent and the Grenadines'),
        'continent' => 'SA',
      ],
      'WS' => [
        'label' => $this->t('Samoa'),
        'continent' => 'OC',
      ],
      'SM' => [
        'label' => $this->t('San Marino'),
        'continent' => 'EU',
      ],
      'ST' => [
        'label' => $this->t('Sao Tome and Principe'),
        'continent' => 'AF',
      ],
      'SA' => [
        'label' => $this->t('Saudi Arabia'),
        'continent' => 'AS',
      ],
      'SN' => [
        'label' => $this->t('Senegal'),
        'continent' => 'AF',
      ],
      'RS' => [
        'label' => $this->t('Serbia'),
        'continent' => 'EU',
      ],
      'SC' => [
        'label' => $this->t('Seychelles'),
        'continent' => 'AF',
      ],
      'SL' => [
        'label' => $this->t('Sierra Leone'),
        'continent' => 'AF',
      ],
      'SG' => [
        'label' => $this->t('Singapore'),
        'continent' => 'AS',
      ],
      'SX' => [
        'label' => $this->t('Sint Maarten (Dutch part)'),
        'continent' => 'SA',
      ],
      'SK' => [
        'label' => $this->t('Slovakia'),
        'continent' => 'EU',
      ],
      'SI' => [
        'label' => $this->t('Slovenia'),
        'continent' => 'EU',
      ],
      'SB' => [
        'label' => $this->t('Solomon Islands'),
        'continent' => 'OC',
      ],
      'SO' => [
        'label' => $this->t('Somalia'),
        'continent' => 'AF',
      ],
      'ZA' => [
        'label' => $this->t('South Africa'),
        'continent' => 'AF',
      ],
      'GS' => [
        'label' => $this->t('South Georgia and the South Sandwich Islands'),
        'continent' => 'SA',
      ],
      'SS' => [
        'label' => $this->t('South Sudan'),
        'continent' => 'AF',
      ],
      'ES' => [
        'label' => $this->t('Spain'),
        'continent' => 'EU',
      ],
      'LK' => [
        'label' => $this->t('Sri Lanka'),
        'continent' => 'AS',
      ],
      'SD' => [
        'label' => $this->t('Sudan'),
        'continent' => 'AF',
      ],
      'SR' => [
        'label' => $this->t('Suriname'),
        'continent' => 'SA',
      ],
      'SJ' => [
        'label' => $this->t('Svalbard and Jan Mayen'),
        'continent' => 'EU',
      ],
      'SE' => [
        'label' => $this->t('Sweden'),
        'continent' => 'EU',
      ],
      'CH' => [
        'label' => $this->t('Switzerland'),
        'continent' => 'EU',
      ],
      'SY' => [
        'label' => $this->t('Syria'),
        'continent' => 'AS',
      ],
      'TW' => [
        'label' => $this->t('Taiwan'),
        'continent' => 'AS',
      ],
      'TJ' => [
        'label' => $this->t('Tajikistan'),
        'continent' => 'AS',
      ],
      'TZ' => [
        'label' => $this->t('Tanzania'),
        'continent' => 'AF',
      ],
      'TH' => [
        'label' => $this->t('Thailand'),
        'continent' => 'AS',
      ],
      'TL' => [
        'label' => $this->t('Timor-Leste'),
        'continent' => 'AS',
      ],
      'TG' => [
        'label' => $this->t('Togo'),
        'continent' => 'AF',
      ],
      'TK' => [
        'label' => $this->t('Tokelau'),
        'continent' => 'OC',
      ],
      'TO' => [
        'label' => $this->t('Tonga'),
        'continent' => 'OC',
      ],
      'TT' => [
        'label' => $this->t('Trinidad and Tobago'),
        'continent' => 'SA',
      ],
      'TN' => [
        'label' => $this->t('Tunisia'),
        'continent' => 'AF',
      ],
      'TR' => [
        'label' => $this->t('Turkey'),
        'continent' => 'AS',
      ],
      'TM' => [
        'label' => $this->t('Turkmenistan'),
        'continent' => 'AS',
      ],
      'TC' => [
        'label' => $this->t('Turks and Caicos Islands'),
        'continent' => 'NA',
      ],
      'TV' => [
        'label' => $this->t('Tuvalu'),
        'continent' => 'OC',
      ],
      'UG' => [
        'label' => $this->t('Uganda'),
        'continent' => 'AF',
      ],
      'UA' => [
        'label' => $this->t('Ukraine'),
        'continent' => 'EU',
      ],
      'AE' => [
        'label' => $this->t('United Arab Emirates'),
        'continent' => 'AS',
      ],
      'GB' => [
        'label' => $this->t('United Kingdom'),
        'continent' => 'EU',
      ],
      'US' => [
        'label' => $this->t('United States'),
        'continent' => 'NA',
      ],
      'UM' => [
        'label' => $this->t('United States Minor Outlying Islands'),
        'continent' => 'OC',
      ],
      'UY' => [
        'label' => $this->t('Uruguay'),
        'continent' => 'SA',
      ],
      'UZ' => [
        'label' => $this->t('Uzbekistan'),
        'continent' => 'AS',
      ],
      'VU' => [
        'label' => $this->t('Vanuatu'),
        'continent' => 'OC',
      ],
      'VE' => [
        'label' => $this->t('Venezuela'),
        'continent' => 'SA',
      ],
      'VN' => [
        'label' => $this->t('Vietnam'),
        'continent' => 'AS',
      ],
      'VG' => [
        'label' => $this->t('Virgin Islands, British'),
        'continent' => 'NA',
      ],
      'VI' => [
        'label' => $this->t('Virgin Islands, US'),
        'continent' => 'NA',
      ],
      'WF' => [
        'label' => $this->t('Wallis and Futuna'),
        'continent' => 'OC',
      ],
      'EH' => [
        'label' => $this->t('Western Sahara'),
        'continent' => 'AF',
      ],
      'YE' => [
        'label' => $this->t('Yemen'),
        'continent' => 'AS',
      ],
      'ZM' => [
        'label' => $this->t('Zambia'),
        'continent' => 'AF',
      ],
      'ZW' => [
        'label' => $this->t('Zimbabwe'),
        'continent' => 'AF',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCountryLabel($country_code): MarkupInterface {
    $country_code = strtoupper($country_code);
    return $this->countries[$country_code]['label'] ?? $this->t('Unknown');
  }

  /**
   * {@inheritdoc}
   */
  public function getContinent($country_code): string {
    $country_code = strtoupper($country_code);
    return $this->countries[$country_code]['continent'] ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function getContinentLabel($continent_code): MarkupInterface {
    $continent_code = strtoupper($continent_code);
    return $this->continents[$continent_code] ?? $this->t('Unknown');
  }

  /**
   * {@inheritdoc}
   */
  public function isValidCountryCode($country_code): bool {
    $country_code = strtoupper($country_code);
    return isset($this->countries[$country_code]);
  }

}
