<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

use app\commands\Commons;

class UploadForm extends model {

    /**
     * 文件
     */
    public $imageFile;

    public function rules() {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'jpg,png,gif']
        ];
    }

    public function upload() {
        if ($this->validate()) {
            $name = $this->imageFile->name;
            $fileType = substr($name, strripos($name,'.')+1);
            $fileName = Commons::createUUID().'.'.$fileType;
            $path = 'uploads/'.$fileName;  // 保存文件路径
            $avator = 'avator/'.$fileName;  // 缩略图路径
            $transtion =  Yii::$app->db->beginTransaction();

            // 获取当前用户
            $user = User::find()->where(['id'=>Yii::$app->user->getId()])->one();
            try {
                $this->imageFile->saveAs($path);
                $this->thumbnailImage($path, $avator);   // 压缩缩略图片
                $user->avator = $avator;  // 保存文件路径到数据表中
                if (!$user->save()) {
                    return false;
                }
                $transtion->commit();
            } catch (Exception $e) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * 缩略图生成函数
     * @param $sourcPath 源路径
     * @param $outPath 输出路径
     */
    private function thumbnailImage($sourcPath, $outPath) {
        $imagine = new Imagine();
        $size = new Box(40, 40);
        $mode = ImageInterface::THUMBNAIL_OUTBOUND;

        $imagine->open($sourcPath)
            ->thumbnail($size, $mode)
            ->save($outPath);
    }
}
?>
