# Bahri Canlı Connect

WhatsApp Business gelen kutusu WordPress eklentisi. **Message Manager**
(`message-manager.tr`) çekirdek API'sine tenant API anahtarıyla bağlanır.

Ana platform reposu: `git@bitbucket.org:bahricanli/message-manager.git`
Bu repo: `git@github.com:bahricanli/bahricanli-connect.git`

## Mimari

- İnce istemci: iş mantığı çekirdekte. Eklenti kendi tablosunu tutmaz — yalnız
  `bahricanli_connect_settings` option'ı (API adresi + anahtar).
- Tüm çağrılar **sunucu tarafında** (`wp_remote_request`) yapılır; API anahtarı
  tarayıcıya düşmez.
- `admin-ajax.php` proxy uçları nonce + `manage_options` doğrular:
  `bahrco_test_connection`, `bahrco_conversations`, `bahrco_messages`,
  `bahrco_send_message`.

## Dosya yapısı

```
bahricanli-connect.php          Ana dosya (header + bootstrap)
includes/
  class-bahrco-plugin.php       Tekil örnek, hook kaydı, ayar okuma
  class-bahrco-api-client.php    Çekirdek API istemcisi (wp_remote_*)
  class-bahrco-admin.php         Menü, ayar kaydı, sayfa çıktıları, asset
  class-bahrco-ajax.php          admin-ajax proxy uçları
admin/views/                     settings / inbox / not-configured
assets/js/admin.js               Ayar testi + gelen kutusu (vanilla JS)
assets/css/admin.css
```

## Çekirdek API uçları (kullanılan)

| Uç | Amaç |
|---|---|
| `GET /api/v1/ping` | bağlantı testi |
| `GET /api/v1/conversations?status=` | konuşma listesi |
| `GET /api/v1/conversations/{id}/messages` | mesajlar |
| `POST /api/v1/conversations/{id}/messages` | serbest metin gönder |

Kimlik: `Authorization: Bearer mm_xxx.<secret>`.

## Yol haritası

- [ ] Şablon gönderimi (pencere kapalıyken)
- [ ] Kişi/etiket ekranı
- [ ] Embedded Signup (numara bağlama) eklentiden tetikleme
- [ ] Medya mesajları
