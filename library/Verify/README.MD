
# 学校验证

### 定义格式

以学校缩写命名文件名 `SCHOOLNAME.php`

```php
<?php
namespace Verify;
#Verify 命名空间下
#继承自Connect接口

class SCHOOLNAME extends Connect
{
	const ID = 123456; //学校唯一id与数据库一致

	/**
	 * 获取真实姓名[必须实现的接口]
	 * @method getName
	 * @param  [type]  $number [学号]
	 * @param  [type]  $pwd    [密码]
	 * @return [type]  string  [NAME]
	 */
	public static function getName($number, $pwd)
	{
		/**/
		return $USER_NAME; 
	}
}
```
如果需要使用验证码

```php
class SCHOOLNAME extends Connect
{
	const ID = 123456; //学校ID
	/**
	 * 获取真实姓名[必须实现的接口]
	 * @method getName
	 * @param  [type]  $number [学号]
	 * @param  [type]  $pwd    [密码]
	 * @param  [type]  $code   [验证码]
	 * @return [type]  string  [NAME]
	 */
	public static function getName($number, $pwd,$code)
	{
		//实现验证并获取姓名
		return $USER_NAME; 
	}

	/**
	 * 获取验证码
	 * @method getCode
	 * @return 
	 *	数组：'img'=>获取的二进制图像
	 *  cookie 'verify_cookie'=>base64编码的cookie
	 */
	public static function getCode()
	{
		return ['img' => $img, 'verify_cookie' => base64_encode($_cookie)];
	}
}
```

### 文件说明

* NKU.php 南开大学验证 
* TJU.php 天津大学验证
* HEBUT.php 河北工业大学验证
* TIFERT.php 天津商职验证
....