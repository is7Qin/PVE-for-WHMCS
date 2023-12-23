# PVE-WHMCS

- 使用自定义的CPU/RAM/VLAN/On-boot/Bandwidth等配置VM/CT计划
- 通过[WHMCS](https://www.whmcs.com/tour/)轻松在[Proxmox VE](https://proxmox.com/en/proxmox-virtual-environment/features)中自动供应VM和CT
- 在WHMCS客户区域查看/管理VM
- 通过WHMCS创建/暂停/解除暂停/终止
- 客户区域提供服务的统计/图形 🙂

## 🎯 模块：系统要求（PVE/WHMCS）

新业务：新安装/使用WHMCS的业务需要注意服务ID < 100的情况。

**SID >100：** WHMCS服务ID要求是关键的，因为Proxmox保留VMID <100（系统）。

- （WHMCS）v8.x.x稳定版（需要HTTPS）
- （WHMCS）**服务ID大于100**
- （PHP）v8.1
- （Proxmox）VE v8.x
- （Proxmox）2个用户（API/VNC）

_如果在WHMCS（DB：tblhosting.id）中没有足够的服务，请创建足够的虚拟/测试条目以达到服务ID>=101。_ **否则，您可能会看到以下错误:** `HTTP/1.1 400 Parameter verification failed. (invalid format - value does not look like a valid VM ID)`

## ✅ 模块：安装和配置


首先，上传并启用模块。

完成所有这些之后，为了使模块正常工作，您需要：

0. 在PVE按照以下教程创建一个VNC用户
1. WHMCS Admin > Config > Servers > 添加PVE主机（用户：root；IP：PVE's）
2. WHMCS Admin > Addons > Proxmox VE for WHMCS > 模块配置 > VNC密钥（见下文）
3. WHMCS Admin > Addons > Proxmox VE for WHMCS > 添加KVM/LXC计划
4. WHMCS Admin > Addons > Proxmox VE for WHMCS > 添加IP池
5. WHMCS Admin > Config > Products/Services > 新服务（创建提供）
6. " " > 新添加的服务 > Tab 3 > **保存**（将模块计划链接到WHMCS服务类型）

## 🥽 noVNC：控制台隧道（客户区域）

在分叉模块之后，我们考虑如何通过WHMCS改进控制台隧道的安全性。我们决定实施一种使用Proxmox VE中具有非常严格权限的辅助用户的路由方法。这需要更多的工作使其正常工作，但提高了安全性。

### 通过WHMCS客户区域提供VNC

1. 正确安装和配置模块
2. 遵循下面的PVE用户要求信息
3. PVE的公共IPv4（或代理到私有）
4. PVE和WHMCS位于同一域名*
5. PVE地址的有效PTR/rDNS


- 注意＃1 = 您必须在相同域名的不同子域上使用Cookie（防止CSRF）。
- 注意＃2 = 如果您的域名具有2部分TLD（即.co.uk），则需要分叉和修改`novnc_router.php` - 理想情况下，我们/某人将优化此以更好地满足所有格式的要求。

## 👥 PVE：用户要求（API和VNC）

**您必须拥有根帐户才能使用该模块。** 通过WHMCS > 服务器配置。

此外，为了提高安全性，对于VNC，您还必须拥有受限用户。在_模块_中配置。

### 在PVE中创建VNC用户

1. 通过PVE > 数据中心/权限/组创建用户组“VNC”
2. 创建新用户“vnc” > 通过PVE > 数据中心/权限/用户选择组：“VNC”，领域：pve
3. 创建新角色 -> 通过PVE > 数据中心/权限/角色选择名称：“VNC”，特权：VM.Console（仅此）
4. 添加访问VNC的权限 -> 通过PVE > 节点/VM/权限/添加组权限选择组：“VNC”，角色：“VNC”
5. 使用“vnc”密码配置WHMCS > 模块 > Proxmox VE for WHMCS > 模块配置 > VNC秘密。

> 不要设置较不严格的权限。上述设计用于增强超级用户的安全性。

## ⚙️ VM/CT计划：设置一切

这些步骤解释了每个选项的独特要求。

自定义字段：值需要放在名称和选择选项中。

> **不确定？** 参考zMANUAL-PVE4.pdf _legacy_手册文件。

### VM选项1：使用PVE模板VM的KVM

首先，在PVE中创建模板。您需要其唯一的PVE ID。

在自定义字段`KVMTemplate`中使用该ID，如`ID|Name`。

> 注意：“Name”是在WHMCS客户区域中显示的内容。

### VM选项2：KVM，使用WHMCS计划+ PVE ISO

首先，在WHMCS模块中创建计划。然后，在WHMCS配置 > 服务。

在服务下，您需要添加一个具有完整

位置的自定义字段`ISO`。

### CT选项：LXC，使用PVE模板文件

首先，在PVE中存储模板。您需要其唯一的文件名。

在自定义字段`Template`中使用该完整文件名，如：

`ubuntu-99.99-standard_amd64.tar.gz|Ubuntu 99`

然后为CT的root用户创建第二个自定义字段`Password`。

## 🌐 IPv4/v6：网络（IP池）

请确保创建具有足够范围/大小的IP池，以能够为VM和CT部署地址。否则，它将无法为您创建服务。

**PVE主机的私有IP：**请注意，由于Proxmox v8.0（严格的same-site属性）引入了严格的要求，VNC可能会在没有工作的情况下导致问题。

### IPv6：尚未生效！ 😐

根据The-Network-Crew/Proxmox-VE-for-WHMCS#33，此模块目前尚未支持IPv6。

当然，您可以通过PVE/`pvesh`手动添加，但截至2023年末，它尚未得到模块的支持。

## 💅 功能：PVE v8.0/8.1的亮点

在v8分支中，Proxmox VE上游部署了一些令人兴奋的新功能，应该添加到此模块中。

### Proxmox v8.0

1. 通过API和Web UI为PCI和USB设备创建、管理和分配资源映射，以供虚拟机（VM）使用。
2. （完成）基于x86-64 psABI微架构级别添加虚拟机CPU模型，并使用广泛支持的x86-64-v2-AES作为通过Web UI创建的新VM的默认值。

### Proxmox v8.1

1. 安全启动支持。
2. 软件定义的网络（SDN）。
3. 新的灵活通知系统（SMTP和Gotify）。
4. MAC组织唯一标识符（OUI）BC:24:11:前缀！

参考：https://pve.proxmox.com/wiki/Roadmap

## 🔄 更新：修补模块

WHMCS管理 > 插件模块 > Proxmox VE for WHMCS > 支持/健康显示更新。

您可以下载新版本并覆盖安装，然后运行任何需要的SQL操作。

请参阅[UPDATE-SQL.md](https://github.com/The-Network-Crew/Proxmox-VE-for-WHMCS/blob/master/UPDATE-SQL.md)文件，打开您的WHMCS DB并运行语句。然后您就完成了。

## 🖥️ INC：库和依赖关系

- （MIT）[PVE2 API的PHP客户端](https://github.com/CpuID/pve2-api-php-client)（2022年12月5日）
- （GPLv2）[TigerVNC VncViewer.jar](https://sourceforge.net/projects/tigervnc/files/stable/)（存储库中的v1.13.1）
- （MPLv2）[noVNC HTML5 Viewer](https://github.com/novnc/noVNC)（存储库中的v1.4.0）
- （GPLv3）[SPICE HTML5 Viewer](https://gitlab.freedesktop.org/spice/spice-html5)（存储库中的v0.3）
- （MIT）[IPv4/SN验证](https://github.com/tapmodo/php-ipv4/)（2012年8月）

## 📄 DIY：文档和资源

- Proxmox API：https://pve.proxmox.com/pve-docs/api-viewer/
- TigerVNC：https://github.com/TigerVNC/tigervnc/wiki
- noVNC：https://github.com/novnc/noVNC/wiki
- WHMCS：https://developers.whmcs.com/
- x86-64-ABI：[最新PDF下载](https://gitlab.com/x86-psABIs/x86-64-ABI/-/jobs/artifacts/master/raw/x86-64-ABI/abi.pdf?job=build)

## 使用许可证（GPLv3）

_**此模块根据GNU通用公共许可证（GPL）v3.0许可。**_

GPLv3：https://www.gnu.org/licenses/gpl-3.0.txt（由自由软件基金会提供）