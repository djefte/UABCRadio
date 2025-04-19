<?php

declare(strict_types=1);

namespace Drupal\Tests\visitors\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../../visitors_geoip.install';

/**
 * Tests the hook_update_8228() function.
 *
 * @group visitors
 */
class HookUpdate8228Test extends KernelTestBase {


  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'visitors',
    'visitors_geoip',
    'user',
    'views',
    'charts',
    'block',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->legacyView();
  }

  /**
   * Tests the hook_update_8228() function.
   */
  public function testHookUpdate8228(): void {

    visitors_geoip_update_8228();

    $view = $this->config('views.view.visitors_geoip');
    $view_data = $view->getRawData();
    $this->assertEquals($this->getCurrentView(), $view_data);
  }

  /**
   * Create a legacy view.
   */
  protected function legacyView(): void {
    $yaml = <<<YAML
langcode: en
status: true
dependencies:
  module:
    - visitors
  enforced:
    module:
      - visitors_geoip
id: visitors_geoip
label: 'Visitors GeoIP'
module: views
description: 'Region and City location reports.'
tag: ''
base_table: visitors
base_field: ''
display:
  default:
    id: default
    display_title: Default
    display_plugin: default
    position: 0
    display_options:
      fields:
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          label: 'Unique visitors'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: 0
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
      pager:
        type: mini
        options:
          offset: 0
          items_per_page: 10
          total_pages: null
          id: 0
          tags:
            next: ››
            previous: ‹‹
          expose:
            items_per_page: false
            items_per_page_label: 'Items per page'
            items_per_page_options: '5, 10, 25, 50'
            items_per_page_options_all: false
            items_per_page_options_all_label: '- All -'
            offset: false
            offset_label: Offset
      exposed_form:
        type: basic
        options:
          submit_button: Apply
          reset_button: false
          reset_button_label: Reset
          exposed_sorts_label: 'Sort by'
          expose_sort_order: true
          sort_asc_label: Asc
          sort_desc_label: Desc
      access:
        type: none
        options: {  }
      cache:
        type: tag
        options: {  }
      empty: {  }
      sorts:
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          order: DESC
          expose:
            label: ''
            field_identifier: ''
          exposed: false
      arguments: {  }
      filters:
        bot:
          id: bot
          table: visitors
          field: bot
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: boolean
          operator: '!='
          value: '1'
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
        visitors_date_time:
          id: visitors_date_time
          table: visitors
          field: visitors_date_time
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_date
          operator: between
          value:
            min: to
            max: from
            value: ''
            type: global
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
            min_placeholder: ''
            max_placeholder: ''
            placeholder: ''
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
      style:
        type: table
        options:
          grouping: {  }
          row_class: ''
          default_row_class: true
          columns:
            location_region_1: location_region_1
            location_country_2: location_country_2
            location_country: location_country
            location_region: location_region
            location_country_1: location_country_1
            visitor_id: visitor_id
          default: visitor_id
          info:
            location_region_1:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_country_2:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_country:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_region:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_country_1:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            visitor_id:
              sortable: true
              default_sort_order: desc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
          override: true
          sticky: false
          summary: ''
          empty_table: false
          caption: ''
          description: ''
      row:
        type: fields
        options:
          default_field_elements: true
          inline: {  }
          separator: ''
          hide_empty: false
      query:
        type: views_query
        options:
          query_comment: ''
          disable_sql_rewrite: false
          distinct: false
          replica: false
          query_tags: {  }
      relationships: {  }
      group_by: true
      header: {  }
      footer: {  }
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url.query_args
      tags: {  }
  city_table:
    id: city_table
    display_title: City
    display_plugin: embed
    position: 3
    display_options:
      fields:
        location_country_2:
          id: location_country_2
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Abbreviation
          exclude: true
          alter:
            alter_text: true
            text: '{{ location_country_2|lower }}'
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: false
          abbreviation: true
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: 'Region url'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: _none
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_city_1:
          id: location_city_1
          table: visitors
          field: location_city
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: 'City url'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: _none
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_city:
          id: location_city
          table: visitors
          field: location_city
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: City
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: Unknown
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Country
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: true
          text: false
        location_country_1:
          id: location_country_1
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: City
          exclude: false
          alter:
            alter_text: true
            text: '{{ location_country }} {{ location_city }}, {{ location_country_1 }}'
            make_link: true
            path: 'internal:/visitors/location/city/{{ location_country_2 }}/{{ location_region }}/{{ location_city_1 }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: true
          abbreviation: false
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          label: 'Unique visitors'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: 0
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
      arguments:
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
      defaults:
        fields: false
        arguments: false
      display_description: ''
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url
        - url.query_args
      tags: {  }
  recent_view_table:
    id: recent_view_table
    display_title: 'Recent Views'
    display_plugin: embed
    position: 3
    display_options:
      fields:
        visitors_id:
          id: visitors_id
          table: visitors
          field: visitors_id
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: numeric
          label: 'Visitors ID'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: false
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
        visitors_url:
          id: visitors_url
          table: visitors
          field: visitors_url
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: URL
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        visitors_date_time:
          id: visitors_date_time
          table: visitors
          field: visitors_date_time
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: date
          label: 'Date Time'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          date_format: short
          custom_date_format: ''
          timezone: ''
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: Visitor
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        nothing:
          id: nothing
          table: views
          field: nothing
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: custom
          label: Operations
          exclude: false
          alter:
            alter_text: true
            text: details
            make_link: true
            path: 'internal:/visitors/hits/{{ visitors_id }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: false
      pager:
        type: full
        options:
          offset: 0
          items_per_page: 10
          total_pages: null
          id: 0
          tags:
            next: ››
            previous: ‹‹
            first: '« First'
            last: 'Last »'
          expose:
            items_per_page: false
            items_per_page_label: 'Items per page'
            items_per_page_options: '5, 10, 25, 50'
            items_per_page_options_all: false
            items_per_page_options_all_label: '- All -'
            offset: false
            offset_label: Offset
          quantity: 9
      sorts:
        visitors_id:
          id: visitors_id
          table: visitors
          field: visitors_id
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          order: DESC
          expose:
            label: ''
            field_identifier: ''
          exposed: false
      arguments:
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: lower
          path_case: none
          transform_dash: false
          break_phrase: false
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
        location_city:
          id: location_city
          table: visitors
          field: location_city
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
      filters:
        bot:
          id: bot
          table: visitors
          field: bot
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: boolean
          operator: '!='
          value: '1'
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
        visitors_date_time:
          id: visitors_date_time
          table: visitors
          field: visitors_date_time
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_date
          operator: between
          value:
            min: to
            max: from
            value: ''
            type: global
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
            min_placeholder: ''
            max_placeholder: ''
            placeholder: ''
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
        visitors_path:
          id: visitors_path
          table: visitors
          field: visitors_path
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          operator: starts
          value: ''
          group: 1
          exposed: true
          expose:
            operator_id: visitors_path_op
            label: Path
            description: ''
            use_operator: false
            operator: visitors_path_op
            operator_limit_selection: false
            operator_list: {  }
            identifier: visitors_path
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
              anonymous: '0'
              content_editor: '0'
              administrator: '0'
            placeholder: ''
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
      filter_groups:
        operator: AND
        groups:
          1: AND
      defaults:
        pager: false
        group_by: false
        fields: false
        sorts: false
        arguments: false
        filters: false
        filter_groups: false
        header: false
      group_by: false
      display_description: ''
      header:
        result:
          id: result
          table: views
          field: result
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: result
          empty: false
          content: 'Displaying @start - @end of @total'
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url
        - url.query_args
      tags: {  }
  region_table:
    id: region_table
    display_title: Region
    display_plugin: embed
    position: 2
    display_options:
      fields:
        location_region_1:
          id: location_region_1
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: 'Region link'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: false
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: _none
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_country_2:
          id: location_country_2
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Abbreviation
          exclude: true
          alter:
            alter_text: true
            text: '{{ location_country_2|lower }}'
            make_link: false
            path: 'internal:/visitors/location/region/{{ location_country_2 }}/{{ location_region }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: false
          abbreviation: true
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Flag
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: false
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: true
          text: false
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: Region
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: Unknown
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_country_1:
          id: location_country_1
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Region
          exclude: false
          alter:
            alter_text: true
            text: '{{location_country }} {{ location_region }}, {{ location_country_1 }} '
            make_link: true
            path: 'internal:/visitors/location/region/{{ location_country_2 }}/{{ location_region_1 }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: Unknown
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: true
          abbreviation: false
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          label: 'Unique visitors'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: 0
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
      arguments:
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
      defaults:
        fields: false
        arguments: false
      display_description: ''
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url
        - url.query_args
      tags: {  }

YAML;

    $views_array = Yaml::parse($yaml);
    $view = $this->config('views.view.visitors_geoip');
    $view->setData($views_array);
    $view->save();
  }

  /**
   * Get the current view.
   */
  protected function getCurrentView(): array {
    $yaml = <<<YAML
langcode: en
status: true
dependencies:
  module:
    - visitors
  enforced:
    module:
      - visitors_geoip
id: visitors_geoip
label: 'Visitors GeoIP'
module: views
description: 'Region and City location reports.'
tag: ''
base_table: visitors
base_field: ''
display:
  default:
    id: default
    display_title: Default
    display_plugin: default
    position: 0
    display_options:
      fields:
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          label: 'Unique visitors'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: 0
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
      pager:
        type: mini
        options:
          offset: 0
          items_per_page: 10
          total_pages: null
          id: 0
          tags:
            next: ››
            previous: ‹‹
          expose:
            items_per_page: false
            items_per_page_label: 'Items per page'
            items_per_page_options: '5, 10, 25, 50'
            items_per_page_options_all: false
            items_per_page_options_all_label: '- All -'
            offset: false
            offset_label: Offset
      exposed_form:
        type: basic
        options:
          submit_button: Apply
          reset_button: false
          reset_button_label: Reset
          exposed_sorts_label: 'Sort by'
          expose_sort_order: true
          sort_asc_label: Asc
          sort_desc_label: Desc
      access:
        type: none
        options: {  }
      cache:
        type: none
        options: {  }
      empty: {  }
      sorts:
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          order: DESC
          expose:
            label: ''
            field_identifier: ''
          exposed: false
      arguments: {  }
      filters:
        bot:
          id: bot
          table: visitors
          field: bot
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: boolean
          operator: '!='
          value: '1'
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
        visitors_date_time:
          id: visitors_date_time
          table: visitors
          field: visitors_date_time
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_date
          operator: between
          value:
            min: to
            max: from
            value: ''
            type: global
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
            min_placeholder: ''
            max_placeholder: ''
            placeholder: ''
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
      style:
        type: table
        options:
          grouping: {  }
          row_class: ''
          default_row_class: true
          columns:
            location_region_1: location_region_1
            location_country_2: location_country_2
            location_country: location_country
            location_region: location_region
            location_country_1: location_country_1
            visitor_id: visitor_id
          default: visitor_id
          info:
            location_region_1:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_country_2:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_country:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_region:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            location_country_1:
              sortable: false
              default_sort_order: asc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
            visitor_id:
              sortable: true
              default_sort_order: desc
              align: ''
              separator: ''
              empty_column: false
              responsive: ''
          override: true
          sticky: false
          summary: ''
          empty_table: false
          caption: ''
          description: ''
      row:
        type: fields
        options:
          default_field_elements: true
          inline: {  }
          separator: ''
          hide_empty: false
      query:
        type: views_query
        options:
          query_comment: ''
          disable_sql_rewrite: false
          distinct: false
          replica: false
          query_tags: {  }
      relationships: {  }
      group_by: true
      header: {  }
      footer: {  }
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url.query_args
      tags: {  }
  city_table:
    id: city_table
    display_title: City
    display_plugin: embed
    position: 3
    display_options:
      fields:
        location_country_2:
          id: location_country_2
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Abbreviation
          exclude: true
          alter:
            alter_text: true
            text: '{{ location_country_2|lower }}'
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: false
          abbreviation: true
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: 'Region url'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: _none
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_city_1:
          id: location_city_1
          table: visitors
          field: location_city
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: 'City url'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: _none
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_city:
          id: location_city
          table: visitors
          field: location_city
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: City
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: Unknown
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Country
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: true
          text: false
        location_country_1:
          id: location_country_1
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: City
          exclude: false
          alter:
            alter_text: true
            text: '{{ location_country }} {{ location_city }}, {{ location_country_1 }}'
            make_link: true
            path: 'internal:/visitors/location/city/{{ location_country_2 }}/{{ location_region }}/{{ location_city_1 }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: true
          abbreviation: false
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          label: 'Unique visitors'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: 0
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
      arguments:
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
      defaults:
        fields: false
        arguments: false
      display_description: ''
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url
        - url.query_args
      tags: {  }
  recent_view_table:
    id: recent_view_table
    display_title: 'Recent Views'
    display_plugin: embed
    position: 3
    display_options:
      fields:
        visitors_id:
          id: visitors_id
          table: visitors
          field: visitors_id
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: numeric
          label: 'Visitors ID'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: false
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
        visitors_url:
          id: visitors_url
          table: visitors
          field: visitors_url
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: URL
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        visitors_date_time:
          id: visitors_date_time
          table: visitors
          field: visitors_date_time
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: date
          label: 'Date Time'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          date_format: short
          custom_date_format: ''
          timezone: ''
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: Visitor
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        nothing:
          id: nothing
          table: views
          field: nothing
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: custom
          label: Operations
          exclude: false
          alter:
            alter_text: true
            text: details
            make_link: true
            path: 'internal:/visitors/hits/{{ visitors_id }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: false
      pager:
        type: full
        options:
          offset: 0
          items_per_page: 10
          total_pages: null
          id: 0
          tags:
            next: ››
            previous: ‹‹
            first: '« First'
            last: 'Last »'
          expose:
            items_per_page: false
            items_per_page_label: 'Items per page'
            items_per_page_options: '5, 10, 25, 50'
            items_per_page_options_all: false
            items_per_page_options_all_label: '- All -'
            offset: false
            offset_label: Offset
          quantity: 9
      sorts:
        visitors_id:
          id: visitors_id
          table: visitors
          field: visitors_id
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          order: DESC
          expose:
            label: ''
            field_identifier: ''
          exposed: false
      arguments:
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: lower
          path_case: none
          transform_dash: false
          break_phrase: false
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
        location_city:
          id: location_city
          table: visitors
          field: location_city
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
      filters:
        bot:
          id: bot
          table: visitors
          field: bot
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: boolean
          operator: '!='
          value: '1'
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
        visitors_date_time:
          id: visitors_date_time
          table: visitors
          field: visitors_date_time
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_date
          operator: between
          value:
            min: to
            max: from
            value: ''
            type: global
          group: 1
          exposed: false
          expose:
            operator_id: ''
            label: ''
            description: ''
            use_operator: false
            operator: ''
            operator_limit_selection: false
            operator_list: {  }
            identifier: ''
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
            min_placeholder: ''
            max_placeholder: ''
            placeholder: ''
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
        visitors_path:
          id: visitors_path
          table: visitors
          field: visitors_path
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          operator: starts
          value: ''
          group: 1
          exposed: true
          expose:
            operator_id: visitors_path_op
            label: Path
            description: ''
            use_operator: false
            operator: visitors_path_op
            operator_limit_selection: false
            operator_list: {  }
            identifier: visitors_path
            required: false
            remember: false
            multiple: false
            remember_roles:
              authenticated: authenticated
              anonymous: '0'
              content_editor: '0'
              administrator: '0'
            placeholder: ''
          is_grouped: false
          group_info:
            label: ''
            description: ''
            identifier: ''
            optional: true
            widget: select
            multiple: false
            remember: false
            default_group: All
            default_group_multiple: {  }
            group_items: {  }
      filter_groups:
        operator: AND
        groups:
          1: AND
      defaults:
        pager: false
        group_by: false
        fields: false
        sorts: false
        arguments: false
        filters: false
        filter_groups: false
        header: false
      group_by: false
      display_description: ''
      header:
        result:
          id: result
          table: views
          field: result
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: result
          empty: false
          content: 'Displaying @start - @end of @total'
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url
        - url.query_args
      tags: {  }
  region_table:
    id: region_table
    display_title: Region
    display_plugin: embed
    position: 2
    display_options:
      fields:
        location_region_1:
          id: location_region_1
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: 'Region link'
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: false
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: _none
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_country_2:
          id: location_country_2
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Abbreviation
          exclude: true
          alter:
            alter_text: true
            text: '{{ location_country_2|lower }}'
            make_link: false
            path: 'internal:/visitors/location/region/{{ location_country_2 }}/{{ location_region }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: false
          abbreviation: true
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Flag
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: false
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: true
          text: false
        location_region:
          id: location_region
          table: visitors
          field: location_region
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: standard
          label: Region
          exclude: true
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: Unknown
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
        location_country_1:
          id: location_country_1
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: visitors_country
          label: Region
          exclude: false
          alter:
            alter_text: true
            text: '{{location_country }} {{ location_region }}, {{ location_country_1 }} '
            make_link: true
            path: 'internal:/visitors/location/region/{{ location_country_2 }}/{{ location_region_1 }}'
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: Unknown
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          icon: false
          text: true
          abbreviation: false
        visitor_id:
          id: visitor_id
          table: visitors
          field: visitor_id
          relationship: none
          group_type: count_distinct
          admin_label: ''
          plugin_id: standard
          label: 'Unique visitors'
          exclude: false
          alter:
            alter_text: false
            text: ''
            make_link: false
            path: ''
            absolute: false
            external: false
            replace_spaces: false
            path_case: none
            trim_whitespace: false
            alt: ''
            rel: ''
            link_class: ''
            prefix: ''
            suffix: ''
            target: ''
            nl2br: false
            max_length: 0
            word_boundary: true
            ellipsis: true
            more_link: false
            more_link_text: ''
            more_link_path: ''
            strip_tags: false
            trim: false
            preserve_tags: ''
            html: false
          element_type: ''
          element_class: ''
          element_label_type: ''
          element_label_class: ''
          element_label_colon: true
          element_wrapper_type: ''
          element_wrapper_class: ''
          element_default_classes: true
          empty: ''
          hide_empty: false
          empty_zero: false
          hide_alter_empty: true
          set_precision: false
          precision: 0
          decimal: .
          separator: ','
          format_plural: 0
          format_plural_string: !!binary MQNAY291bnQ=
          prefix: ''
          suffix: ''
      arguments:
        location_country:
          id: location_country
          table: visitors
          field: location_country
          relationship: none
          group_type: group
          admin_label: ''
          plugin_id: string
          default_action: ignore
          exception:
            value: all
            title_enable: false
            title: All
          title_enable: false
          title: ''
          default_argument_type: fixed
          default_argument_options:
            argument: ''
          summary_options:
            base_path: ''
            count: true
            override: false
            items_per_page: 25
          summary:
            sort_order: asc
            number_of_records: 0
            format: default_summary
          specify_validation: false
          validate:
            type: none
            fail: 'not found'
          validate_options: {  }
          glossary: false
          limit: 0
          case: none
          path_case: none
          transform_dash: false
          break_phrase: false
      defaults:
        fields: false
        arguments: false
      display_description: ''
      display_extenders: {  }
    cache_metadata:
      max-age: -1
      contexts:
        - 'languages:language_interface'
        - url
        - url.query_args
      tags: {  }
YAML;
    $view_array = Yaml::parse($yaml);
    return $view_array;
  }

}
