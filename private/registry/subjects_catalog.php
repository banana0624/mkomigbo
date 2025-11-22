<?php
declare(strict_types=1);
/**
 * project-root/private/registry/subjects_catalog.php
 *
 * Static list of all subjects on the Mkomigbo project.
 * Used by both public and staff sections when database lookup is not required.
 */

if (!function_exists('subjects_catalog')) {
    function subjects_catalog(): array {
        return [
            'history' => [
                'id'   => 1,
                'name' => 'History',
                'meta_description' => 'Historical evolution, heritage, and origin of Mkomigbo and its people.',
            ],
            'slavery' => [
                'id'   => 2,
                'name' => 'Slavery',
                'meta_description' => 'Discussions and records related to slavery and its impact.',
            ],
            'people' => [
                'id'   => 3,
                'name' => 'People',
                'meta_description' => 'Profiles, stories, and notable figures of Mkomigbo.',
            ],
            'persons' => [
                'id'   => 4,
                'name' => 'Persons',
                'meta_description' => 'Biographies and individual records.',
            ],
            'culture' => [
                'id'   => 5,
                'name' => 'Culture',
                'meta_description' => 'Customs, traditions, and lifestyle of the Mkomigbo people.',
            ],
            'religion' => [
                'id'   => 6,
                'name' => 'Religion',
                'meta_description' => 'Faith, beliefs, and spiritual practices in Mkomigbo.',
            ],
            'spirituality' => [
                'id'   => 7,
                'name' => 'Spirituality',
                'meta_description' => 'Philosophical and spiritual heritage of Mkomigbo.',
            ],
            'tradition' => [
                'id'   => 8,
                'name' => 'Tradition',
                'meta_description' => 'Cultural traditions and ceremonies.',
            ],
            'language1' => [
                'id'   => 9,
                'name' => 'Language1',
                'meta_description' => 'Primary linguistic structure of Mkomigbo language.',
            ],
            'language2' => [
                'id'   => 10,
                'name' => 'Language2',
                'meta_description' => 'Advanced and secondary linguistic forms.',
            ],
            'struggles' => [
                'id'   => 11,
                'name' => 'Struggles',
                'meta_description' => 'Struggles, resistance, and resilience of the people.',
            ],
            'biafra' => [
                'id'   => 12,
                'name' => 'Biafra',
                'meta_description' => 'Historical context and experiences related to Biafra.',
            ],
            'nigeria' => [
                'id'   => 13,
                'name' => 'Nigeria',
                'meta_description' => 'Relations and experiences within Nigeria.',
            ],
            'ipob' => [
                'id'   => 14,
                'name' => 'IPOB',
                'meta_description' => 'Information and perspectives on IPOB.',
            ],
            'africa' => [
                'id'   => 15,
                'name' => 'Africa',
                'meta_description' => 'Mkomigbo’s place in the larger African context.',
            ],
            'uk' => [
                'id'   => 16,
                'name' => 'UK',
                'meta_description' => 'Diaspora connections and relations with the United Kingdom.',
            ],
            'europe' => [
                'id'   => 17,
                'name' => 'Europe',
                'meta_description' => 'Interactions and relations with European regions.',
            ],
            'arabs' => [
                'id'   => 18,
                'name' => 'Arabs',
                'meta_description' => 'Historical and modern interactions with the Arab world.',
            ],
            'about' => [
                'id'   => 19,
                'name' => 'About',
                'meta_description' => 'General overview and background of the Mkomigbo project.',
            ],
        ];
    }
}
