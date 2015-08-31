云印新版服务接口
====================

## auth

登录验证
```
POST /auth/
{number:'学号',password:'密码',sch_id:'学校ID，可选'}
```

## user

#### /user/:id

获取用户信息
````
GET /user/123
````

修改信息(密码)
```
PUT /user/123
{password:'md5($password)',old:'md5(old)'}
```

#### /user/:id/phone

获取真实手机号
```
GET /user/123/phone
```

发送手机验证码
```
POST /user/123/phone
{phone:'手机号'}
```

修改绑定手机
```
PUT /user/123/phone
{code:'验证码'}
```

#### /user/:id/email

获取真实邮箱
```
GET /user/123/email
```

绑定邮箱
```
POST /user/123/email
{email:'邮箱地址'}
```

## file

### /file/

获取文件列表
```
GET /file/
```

上传文件(客户端上传的方案)
```
POST /file/
{key:'上传凭证'}
```
### /file/token

获取上传token
返回key和token
```
POST /file/token
{name:'上传的文件名'}
```

删除token（放弃上传）
```
DELETE /file/token/:key
```

### /file/id
获取详细信息
```
GET /file/123
```

修改文件名
```
PUT /file/123
{name:'新的文件名'}
```

删除文件
```
DELETE /file/123
```


## task

### /task/
打印任务列表
```
GET /task/
```

添加打印任务
```
POST /task/
{fid='文件id',pri_id='打印店id',copies="份数",color="颜色",isdouble="双面",ppt="ppt类型",requrements="要求"}
```

### task/:id
修改打印任务
```
PUT /task/1234
{pri_id="打印店id",copies="份数",color="彩印",isdouble="双面",ppt="ppt类型",requrements="要求"}
```

删除打印任务
```
DELETE /task/1234
```

## school

#### /school/
获取学校列表
```
GET /school/
```

#### /school/:id

获取学校详情
```
GET /school/1
```

## printers

#### /printers/
获取打印店列表
(sch_id可选,筛选学校)
```
GET /printers/?sch_id=1
```

#### /printers/id
获取打印店详情
```
GET /printers/12
```

## share

#### /share/
获取当前用户的分享列表
```
GET /share/
```

TODO 搜索

```
POST /share/
{fid:'文件id',name:'分享文件名',detail:'说明信息',anonymous='是否匿名'}
```

#### /share/:id
分享详情
```
GET /share/123
```

修改分享
```
PUT /share/123
{fid:'文件id',name:'分享文件名',detail:'说明信息',anonymous='是否匿名'}
```

删除分享
```
DElETE /share/123
```

TODO
POST /share/:id/up
DElETE /share/:id/up
POST /share/:id/down
DElETE /share/:id/down
 