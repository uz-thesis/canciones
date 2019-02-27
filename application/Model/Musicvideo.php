<?php

/**
 * Class Musicvideo
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Canciones\Model;

use Canciones\Core\Model;
use Canciones\Libs\Helper;

class Musicvideo extends Model
{
    /**
     * Get all music videos from database
     */
    public function getAllMusicVideos()
    {
        return $this->rtdb->getReference('musicVideos')->getValue();
    }

    /**
     * Add a music video to database
     * Please note that it's not necessary to "clean" our input in any way. With PDO all input is escaped properly
     * automatically. We also don't use strip_tags() etc. here so we keep the input 100% original (so it's possible
     * to save HTML and JS to the database, which is a valid use case). Data will only be cleaned when putting it out
     * in the views (see the views for more info).
     * @param int $id ID of the new music video (must be unique)
     * @param string $name Music video name
     * @param string $author Author
     * @param string $desc Description
     * @param string $src Source
     * @param boolean $is_live Live status
     */
    public function addMusicVideo($id, $name, $author, $desc, $src, $is_live)
    {
        $this->rtdb->getReference('musicVideos/' . $id)
             ->set([
                 'musicVideoId' => $id,
                 'musicVideoName' => $name,
                 'musicVideoAuthor' => $author,
                 'musicVideoDesc' => $desc,
                 'musicVideoRuntime' => $runtime,
                 'musicVideoSrc' => $src,
                 'musicVideoIsLive' => $is_live
             ]);
    }

    /**
     * Delete a music video in the database
     * Please note: this is just an example! In a real application you would not simply let everybody
     * add/update/delete stuff!
     * @param int $id ID of music video
     */
    public function deleteMusicVideo($id)
    {
        return $this->rtdb->getReference('musicVideos/' . $id)->remove();
    }

    /**
     * Get a music video from database
     * @param integer $id ID of music video
     */
    public function getMusicVideo($id)
    {
        return $this->rtdb->getReference('musicVideos/' . $id)->getValue();
    }

    /**
     * Update a music video in database
     * Please note that it's not necessary to "clean" our input in any way. With PDO all input is escaped properly
     * automatically. We also don't use strip_tags() etc. here so we keep the input 100% original (so it's possible
     * to save HTML and JS to the database, which is a valid use case). Data will only be cleaned when putting it out
     * in the views (see the views for more info).
     * @param string $name Music video name
     * @param string $author Author
     * @param string $desc Description
     * @param string $src Source
     * @param boolean $is_live Live status
     */
    public function updateMusicVideo($id, $name, $author, $desc, $is_live)
    {
        $this->rtdb->getReference('musicVideos/' . $id)
             ->update([
                 'musicVideoName' => $name,
                 'musicVideoAuthor' => $author,
                 'musicVideoDesc' => $desc,
                 'musicVideoIsLive' => $is_live
             ]);
    }

    /**
     * Get amount of music videos
     */
    public function getAmountOfMusicVideos()
    {
        return count($this->rtdb->getReference('musicVideos')->getValue());
    }
}
