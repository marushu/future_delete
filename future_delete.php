<?php
/****************************************************************************/

/* ! ▼register_post_typeのregister_meta_box_cbのコールバックここから
	参考URL：http://wpxtreme.jp/how-to-use-custom-post-types-in-wordpress-3-0-beta1
		http://wpdocs.sourceforge.jp/関数リファレンス/add_meta_box
		http://ja.forums.wordpress.org/topic/4693?replies=4
	
*/

/****************************************************************************/
function my_limit_day_meta_box($post){
  add_meta_box('my_limit_day_meta', '表示期限', 'my_limit_day_meta_html', 'one_line_comment', 'normal', 'high');
}
function my_limit_day_meta_html($post, $box){
  $days = get_post_meta($post->ID, 'days', true);
  //var_dump( $post );
  echo wp_nonce_field('my_limit_day_meta', 'my_meta_nonce');
  echo '<p style="font-weight: bold; font-size: 1.4em; color: #016701;">この投稿を表示させる日数は、';
  echo '<input style="font-weight: bold; font-size: 1.8em; width: 5em; text-align: center;" type="text" name="days" value="' . $days .  '" size="10">　日間です。</p>';
  echo '<p style="font-size: 1.2em; color: red;">※ 日数は半角の数字を入力してください。</p>';
}
add_action('save_post', 'my_limit_day_meta_update');
function my_limit_day_meta_update($post_id){
  if(!wp_verify_nonce( $_POST['my_meta_nonce'], 'my_limit_day_meta'))
    return $post_id;

  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
    return $post_id;

  if (isset($_POST['action']) && $_POST['action'] == 'inline-save') {
        return $id;
    }

  if('one_line_comment' == $_POST['post_type']){
    if(!current_user_can('edit_post', $post_id))
      return $post_id;
  }else{
      return $post_id;
  }

  if (isset($_POST['days']) && intval($_POST['days'])) {
        update_post_meta( $post_id, 'days', intval($_POST['days'])) ;
    } else {
        delete_post_meta( $post_id, 'days' ) ; // もしも数字以外のものが入ったら？の時の処理。現在はpost_metaを削除するように設定中。http://php.net/manual/ja/function.intval.phpを参照のこと
    }
}

//
add_filter('manage_edit-one_line_comment_columns', 'my_limit_day_columns');
function my_limit_day_columns($columns){
  $columns = array(
    'cb' => '<input type="checkbox"/>',
    'title' => '1行コメントタイトル',
    'days' => '設定表示期間（日）',
    'caution' => '注意',
    'date' => '日付'
  );	
  return $columns;
}
//
add_action('manage_posts_custom_column', 'my_limit_day_column');
function my_limit_day_column($column){
 global $post;
 $days = get_post_meta($post->ID, 'days', true);
  if('image' == $column) the_post_thumbnail(array(64, 64), 'class=featured-image'); //あったら出る
  elseif ("tag" == $column) the_terms(0, 'span'); //あったら出る
  elseif ("days" == $column) echo get_post_meta($post->ID, 'days', true); //今回はコレが出る
  elseif ("caution" == $column && $days == '') echo '<p style="color: red;">日付未入力</p>';
}
// ▲register_post_typeのregister_meta_box_cbのコールバックここまで


// ▼トップページへ1行コメントを出力させる部分ここから
function my_one_line_comment() {
global $post;
		
		$args = array (
				'post_type' => 'one_line_comment',
				//'taxonomy' => 'span',
				//'post_per_page' => 5,
				'order' => 'ASC',
				//'meta_key' => $disply_limit,
				//'meta_value' => $now,
				//'meta_compare' => '>'
		);
		query_posts($args);
		
		echo '<ul id="one_line" class="clearfix">';
		if(have_posts($args))
		{
		
			while(have_posts()) : the_post();
				
			
			// カスタムフィールド内の数値（日数）を取得
			$date = get_post_meta($post->ID, 'days', true);
			//echo $date . ' 　表示期間の日数を表示<br />';
			
			// 投稿日を取得する
			$post_date = get_the_date('U');
			//echo $post_date . '　$post_date のこと。投稿日のUnixタイムスタンプ<br />';
			
			// 数値（日数）をUnixタイムスタンプへ変更
			$date_span = $date * 24 * 60 * 60;
			//echo $date_span . '　表示期限のUnixタイムスタンプ<br />';
			
			//投稿期限を設定する
			$display_limit = $post_date + $date_span;
			//echo $display_limit . '　$display_limit のこと。表示させる期限のUnixタイムスタンプ<br />';
			
			// サイトの「今日」の日付を取得
			$now = current_time('timestamp', get_option('gmt_offset'));
			//echo $now . '　$now のこと。今日のUnixタイムスタンプ<br />';
			
			// 投稿日と今の差分（端数切り捨て）
			$days_diff = round(($display_limit - $now) / (24*60*60));
			//echo '投稿日と現在の日付の差分は、' . $days_diff . '　日です。';
			
			//投稿期限と現在の日付の比較
			if($days_diff > 0){
				//投稿タイトルを表示
				the_title('<li class="one_line_news">', '</li>');
			}
			
			
		endwhile;
		
		
		} else {
			echo '<li style="color: #666; font-weight: normal; font-size: 1.2em;">最新ニュースはありません。</li>';
		}
		echo '</ul>';
		wp_reset_query();
}
// ▲トップページへ1行コメントを出力させる部分ここまで
?>