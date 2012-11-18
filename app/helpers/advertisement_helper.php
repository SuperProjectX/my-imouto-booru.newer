<?php
class AdvertisementHelper extends ActionView_Helper
{
    public function print_advertisement($ad_type)
    {
        if (CONFIG()->can_see_ads(current_user())) {
            // $ad = Advertisement::random($ad_type);
            // if ($ad)
                // return $this->content_tag("div", $this->link_to($this->image_tag($ad->image_url, array('alt' => "Advertisement", 'width' => $ad->width, 'height' => $ad->height), redirect_advertisement_path($ad)), 'style' => "margin-bottom: 1em;"));
        }
    }
}