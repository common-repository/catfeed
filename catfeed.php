<?php
/*
Plugin Name: CatFeed
Plugin URI: http://eb.lv/2008/11/08/catfeed/
Description: Adds category, tag and comment feed auto-discovery links. Category feed links are added only to category views, tag feed links to tag views and comment feed links to single post views.
Version: 0.1
Author: eb.lv
Author URI: http://eb.lv/
*/

/*  Copyright (C) 2008 eb.lv (http://eb.lv)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
 
  class CatFeed
  {
    function add_config_page()
    {
      global $wpdb;
      if ( function_exists('add_submenu_page') )
      {
        add_submenu_page('options-general.php','CatFeed Configuration', 'CatFeed', 8, basename(__FILE__),array('CatFeed','config_page'));
      }
    }

    function config_page()
    {
      $opt_Type = get_option('CatFeed_Type');
      if ($opt_Type == "")
      {
        $opt_Type = "atom";
      }

      $style = "[blog] &raquo; [name]";
      $style_Category = htmlspecialchars(get_option('CatFeed_CategoryStyle'));
      if ($style_Category == "")
      {
        $style_Category = $style;
      }
      $style_Tag = htmlspecialchars(get_option('CatFeed_TagStyle'));
      if ($style_Tag == "")
      {
        $style_Tag = $style;
      }
      $style_Comment = htmlspecialchars(get_option('CatFeed_CommentStyle'));
      if ($style_Comment == "")
      {
        $style_Comment = $style;
      }
    ?>
      <div class="wrap">
      <h2>CatFeed settings</h2>

      <form method="post" action="options.php">
      <?php wp_nonce_field('update-options'); ?>

      <table class="form-table" style="width:100%;">

      <tr valign="top">
      <th scope="row" style="width:320px;">Feed type</th>
      <td>
      <select name="CatFeed_Type">
      <option value="atom" <?php if ($opt_Type == "atom") echo 'selected="selected"' ?>>atom</option>
      <option value="rss2" <?php if ($opt_Type == "rss2") echo 'selected="selected"' ?>>rss 2.0</option>
      <option value="rss" <?php if ($opt_Type == "rss") echo 'selected="selected"' ?>>rss 0.92</option>
      </select>
      </td>
      </tr>
 
      <tr valign="top">
      <th scope="row">Insert category feeds in category views</th>
      <td><input type="checkbox" name="CatFeed_Category" value="true" <?php if (get_option('CatFeed_Category') == "true") echo 'checked="checked"'; ?> /></td>
      </tr>
 
      <tr valign="top">
      <th scope="row">Category feed title</th>
      <td><input type="text" name="CatFeed_CategoryStyle" value="<?php echo $style_Category; ?>" /> Default: <?php echo $style; ?></td>
      </tr>
 
      <tr valign="top">
      <th scope="row">Insert tag feeds in tag views</th>
      <td><input type="checkbox" name="CatFeed_Tag" value="true" <?php if (get_option('CatFeed_Tag') == "true") echo 'checked="checked"'; ?> /></td>
      </tr>

      <tr valign="top">
      <th scope="row">Tag feed title</th>
      <td><input type="text" name="CatFeed_TagStyle" value="<?php echo $style_Tag; ?>" /> Default: <?php echo $style; ?></td>
      </tr>

      <tr valign="top">
      <th scope="row">Insert comment feeds in single post views</th>
      <td><input type="checkbox" name="CatFeed_Comment" value="true" <?php if (get_option('CatFeed_Comment') == "true") echo 'checked="checked"'; ?> /></td>
      </tr>

      <tr valign="top">
      <th scope="row">Comment feed title</th>
      <td><input type="text" name="CatFeed_CommentStyle" value="<?php echo $style_Comment; ?>" /> Default: <?php echo $style; ?></td>
      </tr>

      </table>

      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="page_options" value="CatFeed_Type,CatFeed_Category,CatFeed_CategoryStyle,CatFeed_Tag,CatFeed_TagStyle,CatFeed_Comment,CatFeed_CommentStyle" />

      <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
      </p>

      </form>
      </div>
    <?php
    }

    function wp_head() 
    {
      if ( (is_category() && get_option('CatFeed_Category') == "true") ||
           (is_tag() && get_option('CatFeed_Tag') == "true") ||
           ((is_single() || is_page()) && get_option('CatFeed_Comment') == "true") )
      {
        echo "<!-- Added by CatFeed plugin http://eb.lv/2008/11/08/catfeed/ -->\n";

        $opt_Type = get_option('CatFeed_Type');
        if ($opt_Type == "")
        {
          $opt_Type = "atom";
        }

        $blog = get_option('blogname');
        $title = "";
        $url = "";

        if (is_category())
        {
          $title = single_cat_title('', false);
          $url = get_category_feed_link(get_query_var('cat'), $opt_Type);
          $style = htmlspecialchars(get_option('CatFeed_CategoryStyle'));
        }
        else if (is_tag())
        {
          $title = single_tag_title('', false);
          $url = get_tag_feed_link(get_query_var('tag_id'), $opt_Type);
          $style = htmlspecialchars(get_option('CatFeed_TagStyle'));
        }
        else
        {
          $title = single_post_title('', false);
          $url = get_post_comments_feed_link('', $opt_Type);
          $style = htmlspecialchars(get_option('CatFeed_CommentStyle'));
        }

        if ($style == "")
        {
          $style = "[blog] &raquo; [name]";
        }

        $title = str_replace(array('[blog]', '[name]'), array($blog, $title), $style);

        if ($opt_Type == "atom")
        {
          echo '<link rel="alternate" type="application/atom+xml" title="'.$title.'" href="'.$url.'" />';
        }
        else
        {
          echo '<link rel="alternate" type="application/rss+xml" title="'.$title.'" href="'.$url.'" />';
        }
        echo "\n\n";
      }
    }
  }

  $_cf = new CatFeed();
  add_action('admin_menu', array($_cf,'add_config_page'));
  add_action('wp_head', array($_cf, 'wp_head'));
?>
