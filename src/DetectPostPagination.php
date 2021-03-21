<?php

/**
 * DetectPostPagination.php
 *
 * @package           WordPressURLDetector
 * @author            Leon Stafford <me@ljs.dev>
 * @license           The Unlicense
 * @link              https://unlicense.org
 */

declare(strict_types=1);

namespace WordPressURLDetector;

/**
 * Class DetectPostPagination
 *
 * @package WordPressURLDetector
 */
class DetectPostPagination
{

    /**
     * Detect Post pagination URLs
     *
     * @return array<string> list of URLs
     */
    // phpcs:ignore NeutronStandard.Functions.LongFunction.LongFunction
    public static function detect( string $wpSiteURL, WPDB $wpdb ): array
    {
        $postTypes = $wpdb->uniquePublishedPostTypes();
        $paginationBase = SiteInfo::getPaginationBase();
        $defaultPostsPerPage = get_option('posts_per_page');

        $urlsToInclude = [];

        // TODO: should be combined into above query
        foreach ($postTypes as $postType) {
            $postTypeTotal = $wpdb->totalPublishedForPostType($postType);

            if (! $postTypeTotal) {
                continue;
            }

            $postTypeObj = get_post_type_object($postType);

            if (! $postTypeObj) {
                continue;
            }

            $pluralForm = strtolower((array)$postTypeObj->labels)['name'];

            // skip post type names containing spaces
            if (strpos($pluralForm, ' ') !== false) {
                continue;
            }

            $totalPages = ceil($postTypeTotal / $defaultPostsPerPage);

            for ($page = 1; $page <= $totalPages; $page += 1) {
                // TODO: skipping page pagination here, but is it covered elsewhere?
                if ($postType === 'page') {
                    continue;
                }

                if ($postType === 'post') {
                    $postArchiveSlug = '';

                    // check if a Posts page has been set in Settings > Reading
                    if (get_option('page_for_posts') !== '0') {
                        // get FQURL to Posts Page
                        $postArchiveLink = get_post_type_archive_link('post');

                        if ($postArchiveLink) {
                            $postArchiveSlug = str_replace(
                                $wpSiteURL,
                                '',
                                trailingslashit($postArchiveLink)
                            );
                        }
                    }

                    $urlsToInclude[] = "/{$postArchiveSlug}{$paginationBase}/{$page}/";
                } else {
                    // TODO: move into separate Detector for custom post types
                    $urlsToInclude[] =
                        "/{$pluralForm}/{$paginationBase}/{$page}/";
                }
            }
        }

        return $urlsToInclude;
    }
}
