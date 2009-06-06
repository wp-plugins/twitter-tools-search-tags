<?php

/*
Plugin Name: Twitter Tools Search Tags
Plugin URI: http://www.scibuff.com/2009/06/05/search-tags-extension-to-the-twitter-tools-wordpress-plugin/
Description: Adds twitter search tags to the Twitter Tools automatic "New Blog Post" status using Twitter Tool's 'tweet_blog_post_url' hook. The plugin will add post tags as twitter search tags to the "New Blog Post" twitter status message (and also shorten the new post's url using http://is.gd API). 
Version: 1.0
Author: Tomas Vorobjov aka Scibuff
Author URI: http://www.scibuff.com
*/

/*  Copyright 2005-2006  Tomas Vorobjov aka SciBuff  (email : blog@scibuff.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

DEFINE( "TWITTER_TOOLS_SEARCH_TAGS_EXTRA_LENGTH", "New blog post: ");

if(!function_exists('twitter_tools_search_tags')) {
	
	$twitter_tools_search_tags_post_id = 1;
	
	/**
	 * This method is executed when a new post is published. Because 
	 * tweet_blog_post_url doesn't send the post id along as a parameter
	 * we need to store the latest's post id to be able to get the
	 * post's title and post's tags from within the twitter_tools_search_tags
	 * function. 
	 * @return void
	 * @param object $post_id The latest post's id
	 */
	function twitter_tools_search_tags_publish_post( $post_id ){
		global $twitter_tools_search_tags_post_id;
		$twitter_tools_search_tags_post_id = $post_id;
	}
	
	/**
	 * Adds twitter search tags to the Twitter Tools "New Blog Post" message. 
	 * @return Shortens the url using http://is.gd API's and adds post's tags
	 * as twitter search tags (i.e. #tag1, #tag2, ...)
	 * @param object $long_url The new post's url
	 */
	function twitter_tools_search_tags( $long_url ){
		
		global $twitter_tools_search_tags_post_id;
		
		$url = twitter_tools_search_tags_shorten_url( $long_url );
		
		$posttags = get_the_tags( $twitter_tools_search_tags_post_id );
		$post_title = get_the_title( $twitter_tools_search_tags_post_id );
		if ($posttags) {
			$status_length = 140 - strlen( TWITTER_TOOLS_SEARCH_TAGS_EXTRA_LENGTH );
			foreach($posttags as $tag) {
				$hash_tag = ' #' . $tag->name; 
				if ( strlen( $url ) + strlen( $post_title ) + strlen( $hash_tag ) < $status_length ){ 
					$url .= $hash_tag;
				}
			}
		}

		return $url;

	}

	/**
	 * Uses the http://is.gd url shortening API to shorten a url
	 * @return shortened url
	 * @param string $url
	 */
	function twitter_tools_search_tags_shorten_url( $url ){
		
		$ch = curl_init(); 
		$timeout = 5; 
		curl_setopt($ch,CURLOPT_URL,'http://is.gd/api.php?longurl='.$url); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
		$data = curl_exec($ch); 
		curl_close($ch); 
		return $data;
			
	}

}

add_action('publish_post', 'twitter_tools_search_tags_publish_post', 1);
add_filter('tweet_blog_post_url', 'twitter_tools_search_tags');

?>