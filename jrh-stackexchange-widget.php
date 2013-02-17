<?php

/**
* StackExchange Questions Widget
*
* This widget will take tags from post or custom fields and make a request to the 
* StackExchange API to search for related questions.
*/

class jrh_stackexchange_widget extends WP_Widget
{
    /**
    * API Settings
    */
    private $_api = "https://api.stackexchange.com";
    private $_apiKey = "";
    private $_apiClientId = "";
    private $_apiSite = "";
    private $_apiPageSize = 5;
    
    /**
    * Widget Initialization
    *
    * This method is called automatically upon registering the widget
    */
    public function jrh_stackexchange_widget()
    {
        $widget_options = array(
            'class_name' => 'jrh_stackexchange_widget',
            'description' => 'Show related questions from StackExchange site.'
        );
        $this->WP_Widget('jrh_stackexchange_widget', 'StackExchange Questions');

        // Load Current API key/client_id
        $options = get_option( 'jrh_stackexchange_options' );
        $this->_apiKey = isset($options['client_key']) ? $options['client_key'] : "";
        $this->_apiClientId = isset($options['client_id']) ? intval($options['client_id']) : "";
        $this->_apiSite = isset($options['site']) ? $options['site'] : "";
    }

    /**
    * Widget Form
    *
    * This method is called automatically when rendering the form in the Widget management area.
    *
    * @param Array  Widget instance
    */
    function form($instance)
    {
        $instance = wp_parse_args( (array) $instance, array( 'title' => 'Related StackExchange Questions', 'question_count' => '3' ) );
        $title = $instance['title'];
        $question_count = $instance['question_count'];

        ?>
            Title:<br>
            <input 
                name = "<?php echo($this->get_field_name('title')); ?>"
                type = "text"
                value = "<?php echo(esc_attr($title)); ?>"
            ><br><br>

            Number of Questions:<br>
            <input 
                name = "<?php echo($this->get_field_name('question_count')); ?>"
                type = "text"
                value = "<?php echo(esc_attr($question_count)); ?>"
            >
        <?
    }
 
    /**
    * Widget Form Update
    *
    * This method is called automatically when saving form for widget instance.
    *
    * @param Array  Changed data in widget instance
    * @param Array  Previous data in widget instance
    * @return Array New values merged into instance
    */
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['question_count'] = $new_instance['question_count'];
        return $instance;
    }

    /**
    * Widget Action
    *
    * This method is called automatically when rendering the widget. This is where we will get the related
    * questions from the StackExchange site.
    *
    * @param Array  Arguments describing widget placement
    * @param Array  Instance of form arguments
    */
    function widget($args, $instance)
    {
        // Set Theme
        wp_register_style( 'jrh_stackexchange_theme', plugins_url('css/theme.css', __FILE__) );
        wp_enqueue_style( 'jrh_stackexchange_theme' );

        // Get global post object and verify
        global $post;
        if (is_object($post))
        {
            if(isset($post->ID) && is_numeric($post->ID) && $post->ID > 0)
            {
                if(!is_admin())
                {
                    if(is_single())
                    {
                        // Get custom fields for post to check for keywords
                        $keywords = array();
                        $fields = get_post_custom($post->ID);
                        if(is_array($fields) && count($fields) > 0)
                        {
                            foreach($fields as $name => $value)
                            {
                                if( stristr($name, "stackexchange_search") )
                                {
                                    foreach($value as $word)
                                    {
                                        $keywords[] = $word;
                                    }
                                }
                            }
                        }

                        if(count($keywords) > 0)
                        {
                            // Get arguments and output title
                            extract($args, EXTR_SKIP);
                            echo $before_widget;
                            $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
                            if (!empty($title))
                                echo $before_title . $title . $after_title;

                            // Get Number of Questions to display
                            $this->_apiPageSize = empty($instance['question_count']) ? 3 : intval($instance['question_count']);

                            // Create list of keywords to pass to API
                            $keywords = implode(",", $keywords);
                        
                            // Make request to StackExchange API
                            $apiCall = $this->_api . "/search/advanced/?order=desc&sort=activity";
                            $apiCall .= "&client_id=$this->_apiClientId&key=$this->_apiKey";
                            $apiCall .= "&site=$this->_apiSite&q=$keywords&pagesize=$this->_apiPageSize";
                            $response = wp_remote_get( $apiCall );

                            if($response['response']['code'] == '200')
                            {
                                $questions = isset($response['body']) ? json_decode($response['body']) : array();
                                $questions = isset($questions->items) ? $questions->items : array();

                                echo("<div class='stackexchange-box'>");
                                foreach($questions as $question)
                                {
                                    echo("<b><a target='_blank' href='$question->link'>$question->title</a></b>");
                                    echo("<span class='stackexchange-author'> asked by <a target='_blank' href='{$question->owner->link}'>{$question->owner->display_name}</a></span><br>");
                                    foreach($question->tags as $tag)
                                    {
                                        echo("<span class='stackexchange-tag'>$tag</span>");
                                    }
                                    echo("<br><br>");
                                }
                                echo("</div>");
                            } else {
                                echo ("Oops! Having trouble talking to the StackExchange API: " . $response['response']['message']);
                            }
                            
                            echo $after_widget;
                        }
                    }
                }
            }
        }
    }
 
}
?>