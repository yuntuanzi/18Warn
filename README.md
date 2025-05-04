# 🌟 18Warn - 趣味年龄验证页面

> 一个炫酷的年龄验证恶作剧页面，专为娱乐设计！ ✨  
> 点击“我已满18周岁”将跳转到“被骗”统计页面，记录你的“上当”瞬间！😈

## 🎨 核心功能

```diff
+ 💫 炫酷粒子动画背景 - 视觉效果拉满
+ 🔄 流畅过渡动画 - 按钮点击加载更自然
+ 🍪 Cookie存储验证状态 - 30天内免重复验证
+ 📱 响应式设计 - 适配手机与电脑
+ 🌕 暗黑/明亮模式切换 - 酷炫体验
+ 📊 被骗排行榜 - 按城市统计访问次数
+ 💬 留言板 - 留下你的“被骗感言”
+ 📸 切换不同国家语言
```

## 🎭 交互结局

| 操作 | 结果 |
|------|------|
| ✅ 点击“我已满18周岁” | 显示验证成功，跳转到被骗统计页面 |
| ❌ 点击“我尚未满18周岁” | 跳转到[国家中小学智慧教育平台](https://basic.smartedu.cn/) |

## 🖼️ 预览

> 预览站点：[https://me.bbb-lsy07.sbs/fun-18-APP/](https://me.bbb-lsy07.sbs/fun-18-APP/)  
> 站长交流群：[967140086](https://qm.qq.com/cgi-bin/qm/qr?k=6ww_haorKSc-F1QWF4JdrErhmFzxeNbo&jump_from=webapi&authKey=7py1srP3pqE94lCPjoh02aQhVPjZuTlYhm+q+yZ4NpTP0WEd46kTWKRau+P5r9ey)

![界面1](https://github.com/user-attachments/assets/e2498acd-0b67-43c0-9713-0b678b655336)
![界面2](https://github.com/user-attachments/assets/0f365d50-dfcb-4e5a-be4c-d8e1a6c6066e)
![界面3](https://github.com/user-attachments/assets/82437273-a963-4b8a-9fab-32fa5c86f32d)

> 暗黑/明亮模式切换按钮位于被骗统计页面右下角

![被骗页面](https://github.com/user-attachments/assets/5618d8e9-554f-4edb-9f95-fd3f502aab61)

> 未通过年龄验证将显示无权限提示

![无权限](https://github.com/user-attachments/assets/a64c7074-906e-46cf-b9b9-21394a2d34ce)

## 🛠️ 环境支持

```bash
系统要求：PHP >= 8.1
部署方式：开箱即用
```

## ⚙️ 自定义配置

```javascript
// 主要配置项
window.location.href = "nb.php"  // 成功验证跳转页面
window.location.href = "https://basic.smartedu.cn/"  // 未成年跳转链接

/* CSS变量 */
:root {
  --primary: #6e45e2;
  --secondary: #88d3ce;
  --danger: #e94560;
}
```

## 🚀 快速开始

1. 克隆仓库：
   ```bash
   git clone https://github.com/bbb-lsy07/18Warn.git
   ```
2. 配置服务器环境（PHP >= 8.1）。
3. 将文件部署到Web服务器，确保`counter.txt`、`rankings.json`、`messages.json`有写权限。
4. 访问`index.html`开始体验！

## 🆕 新增功能

基于原仓库 [yuntuanzi/18Warn](https://github.com/yuntuanzi/18Warn)，本复刻版本新增了以下功能：
- **被骗排行榜**：根据IP定位城市，统计访问次数，展示前三名城市。
- **留言板**：用户可提交最多200字的“被骗感言”，支持频率限制（每IP每分钟1次）。
- **多语言支持**：一键切换不同语言（欢迎贡献"nb.php"的其他语言翻译）
- **性能优化**：使用`DocumentFragment`优化粒子动画，减少DOM操作。
- **SEO优化**：添加元数据，提升搜索引擎友好性。
- **无缓存策略**：通过HTTP头确保页面实时更新。

## ⭐ 喜欢就给个Star！

如果觉得好玩，欢迎到 [GitHub仓库](https://github.com/bbb-lsy07/18Warn) 点个Star支持一下！🌟

## ⚠️ 法律声明

> 本项目仅供娱乐用途，请勿用于非法目的。  
> 使用前请确保遵守当地法律法规。

## 🤝 参与贡献

欢迎提交Pull Request改进项目！  
特别鸣谢：
- 原作者：[yuntuanzi](https://github.com/yuntuanzi)
