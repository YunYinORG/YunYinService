云印新版服务接口
====================

## auth

登录验证
```
POST /auth/
{number:'学号',password:'密码',sch_id:'学校ID，可选'}
```

## user

#### /user/

获取登录信息

```
GET /user/
```
响应
```
{
    "status": 1,
    "info": {
        "msg": "亲爱的同学,已经成功登录",
        "name": "同学",
        "id": "5"
    }
}
```

#### /user/:id

获取用户信息

````
GET /user/123
````

响应
```
{
    "status": 1,
    "info": {
        "name": "姓名",
        "number": "学号",
        "phone": null,
        "email": null,
        "school": "天津大学"
    }
}
```



修改信息(密码)
```
PUT /user/123
{password:'md5($password)',old:'md5(old)'}
```

响应
```
{
    "status": 0,
    "info": "原密码错误"
}
```

#### /user/:id/phone

获取真实手机号
```
GET /user/123/phone
```
响应
```
{
    "status": 1,
    "info": "13888888888"
}

```

发送手机验证码
```
POST /user/123/phone
{phone:'手机号'}
```
响应
```
{
    "status": 1,
    "info": "已发送"
}
```

修改绑定手机
```
PUT /user/123/phone
{code:'验证码'}
```
响应
```
{
    "status": 1,
    "info": "绑定成功"
}
```

#### /user/:id/email

获取真实邮箱
```
GET /user/123/email
```
响应
```
{
    "status": 1,
    "info": "13888888888@139.com"
}

```

绑定邮箱
```
POST /user/123/email
{email:'test@test.cn'}
```
响应
```
{
    "status": 1,
    "info": "验证邮件成功发送至：test@test.cn"
}
```

## file

### /file/token

获取上传token
返回key和token
```
POST /file/token
{name:'上传的文件名'}
```

响应
```
{
    "status": 1,
    "info": {
        "token": "5zyb0hc1R6HVYx_sxfbSz4afdqv7fZeCY0_44QQ6:530opUa1agcnTY_2o1yDc2EnZjk=:eyJzY29wZSI6InVwbG9hZHRlc3Q6dGVtcF81XzU1ZmMzM2Y2OTVjZGIiLCJkZWFkbGluZSI6MTQ0MjU5MjMzNCwiZnNpemVMaW1pdCI6IjIwOTcxNTIwIn0=",
        "key": "temp_5_55fc33f695cdb",
        "name": "12.doc"
    }
}
```

删除token（放弃上传）
```
DELETE /file/token/key/:key
```
响应
```
{
    "status": 1,
    "info": "已经成功取消123.doc"
}
```

### /file/

提交上传文件(客户端上传的方案)
```
POST /file/
{key:'上传凭证'}
```
响应
```
{
    "status": 1,
    "info": {
        "msg": "保存成功",
        "id": "1"
    }
}
```

获取文件列表
```
GET /file/
```
响应
```
{
    "status": 1,
    "info": [
        {
            "id": "1",
            "name": "云印南开用户操作流程.docx",
            "time": "2015-09-18 16:52:42"
        }
    ]
}
```

### /file/id
获取详细信息
```
GET /file/123
```

```
{
    "status": 1,
    "info": {
        "id": "1",
        "use_id": "5",
        "name": "云印南开用户操作流程.docx",
        "url": "http://uploadtest.yunyin.org/file_5_55fc40a641356_1442595162?e=1442595553&attname=%25E4%25BA%2591%25E5%258D%25B0%25E5%258D%2597%25E5%25BC%2580%25E7%2594%25A8%25E6%2588%25B7%25E6%2593%258D%25E4%25BD%259C%25E6%25B5%2581%25E7%25A8%258B.docx&token=5zyb0hc1R6HVYx_sxfbSz4afdqv7fZeCY0_44QQ6:oIqCwp09j_BjtvHI-Qe7yMH5wuc=",
        "time": "2015-09-18 16:52:42"
    }
}
```

修改文件名
```
PUT /file/123
{name:'新的文件名'}
```
响应
```
{
    "status": 1,
    "info": "成功修改为：用户操作流程.docx"
}
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
