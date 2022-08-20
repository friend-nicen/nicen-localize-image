```
/**
Plugin Name: nicen-localize-image
Plugin URI:https://nicen.cn/2893.html
Description: 用于本地化文章的外部图片，启用之后会给新增两个功能：编辑器增加在前端本地化外部图片的插件和文章保存时在后端自动本地化外部图片的功能（区别在于一个是发布前本地化和发布后本地化）
Version: 1.0.0
Author: 友人a丶
Author URI: https://nicen.cn
Text Domain: nicen-localize-image
License: GPLv2 or later
 */
```
# nicen-localize-image

nicen-localize-image，是一款用于自动本地化wordpress文章内外部图片的插件。

Github：[https://github.com/friend-nicen/nicen-localize-image](https://github.com/friend-nicen/nicen-localize-image)

Gitee：[https://github.com/friend-nicen/nicen-localize-image](https://github.com/friend-nicen/nicen-localize-image)

插件提供两种本地化外部图片的模式，两种模式可同时开启，互不冲突；
![alt 属性文本](https://nicen.cn/wp-content/uploads/2022/08/1661002814846.png)
## 编辑器本地化插件
   
启用这个模式之后，会将wordpress文章编辑器切换为经典编辑器，并在编辑器上方新增一个功能图标，点击之后可以自动检测并本地化所有外部图片；


![alt 属性文本](https://nicen.cn/wp-content/uploads/2022/08/1661008460684.png)
![alt 属性文本](https://nicen.cn/wp-content/uploads/2022/08/1661008539461.png)

## 发布时自动本地化

启用这个模式之后会在文章发布时自动本地化所有外部图片；
![alt 属性文本](https://nicen.cn/wp-content/uploads/2022/08/1661008642570.png)


推荐使用【编辑器本地化插件】在发布前进行本地化，当图片数量过多或者文件太大【发布时自动本地化】可能会导致请求卡死。



# 选项说明

## 1.图片本地化时保存到数据库

不开启的话，本地化的时候下载的图片不会在数据库内新增关联信息；开启之后，本地化的图片可以在媒体库内查看，并且可以重复使用；

## 2.发布时图片自动添加alt属性

img标签指定alt属性之后对seo较为友好，您可以选择指定alt的值为文章标题，或者文章分类；

## 3.本地化保存路径

代表本地化下载图片时，文件的保存路径（文件夹要求可写）；