import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/api_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? _profileData;
  bool _isLoading = true;
  bool _biometricEnabled = true;
  bool _notificationEnabled = true;

  @override
  void initState() {
    super.initState();
    _fetchProfile();
  }

  Future<void> _fetchProfile() async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    final result = await ApiService.getProfile();
    if (result['success']) {
      if (mounted) {
        setState(() {
          _profileData = result['data'];
          _isLoading = false;
        });
      }
    } else {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Gagal mengambil data')),
        );
      }
    }
  }

  void _handleLogout() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Keluar Aplikasi', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold)),
        content: Text('Apakah Anda yakin ingin keluar dari aplikasi?', style: GoogleFonts.inter()),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Batal', style: GoogleFonts.inter(color: Colors.grey, fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            onPressed: () async {
              await ApiService.logout();
              if (mounted) {
                Navigator.pushNamedAndRemoveUntil(context, '/login', (route) => false);
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red.shade700,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: Text('Keluar', style: GoogleFonts.inter(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: Color(0xFFF8FAFC),
        body: Center(child: CircularProgressIndicator(color: Color(0xFF1D4ED8))),
      );
    }

    if (_profileData == null) {
      return Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        appBar: AppBar(backgroundColor: const Color(0xFF0037B0), elevation: 0),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.grey),
              const SizedBox(height: 16),
              const Text('Gagal memuat profil'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _fetchProfile, 
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF1D4ED8)),
                child: const Text('Coba Lagi', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0037B0),
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            _buildProfileHeader(),
            _buildInfoCard(),
            const SizedBox(height: 8),
            _buildSettingsSection(),
            const SizedBox(height: 100),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(24, 60, 24, 80),
      decoration: const BoxDecoration(
        color: Color(0xFF0037B0),
        borderRadius: BorderRadius.only(bottomLeft: Radius.circular(32), bottomRight: Radius.circular(32)),
      ),
      child: Column(
        children: [
          Stack(
            children: [
              Container(
                width: 96,
                height: 96,
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white.withOpacity(0.2), width: 4),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 20)],
                ),
                child: Center(
                  child: Text(
                    _profileData!['inisial'] ?? '??',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 32,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF0037B0),
                    ),
                  ),
                ),
              ),
              Positioned(
                bottom: 0,
                right: 0,
                child: Container(
                  padding: const EdgeInsets.all(6),
                  decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                  child: const Icon(Icons.edit, size: 14, color: Color(0xFF0037B0)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            _profileData!['nama_lengkap'],
            textAlign: TextAlign.center,
            style: GoogleFonts.plusJakartaSans(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  children: [
                    const Text('MEMBER ID ', style: TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold)),
                    Text(
                      _profileData!['no_anggota'],
                      style: GoogleFonts.jetBrainsMono(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFF006C4A),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  _profileData!['status'].toUpperCase(),
                  style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Transform.translate(
        offset: const Offset(0, -40),
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [BoxShadow(color: const Color(0xFF111C2D).withOpacity(0.06), blurRadius: 32, offset: const Offset(0, 12))],
          ),
          child: Column(
            children: [
              _infoItem(Icons.corporate_fare, 'KOPERASI', 'Lumbung Artha Sejahtera'),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(child: _infoDetail('CABANG', _profileData!['cabang'])),
                  Container(width: 1, height: 32, color: const Color(0xFFF0F3FF)),
                  const SizedBox(width: 20),
                  Expanded(child: _infoDetail('BERGABUNG', _profileData!['tanggal_masuk'], subValue: _profileData!['durasi'])),
                ],
              ),
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 24),
                child: Divider(color: Color(0xFFF0F3FF), height: 1),
              ),
              _contactItem(Icons.call, 'NO. HP', _profileData!['no_hp']),
              const SizedBox(height: 20),
              _contactItem(Icons.mail, 'EMAIL', _profileData!['email']),
            ],
          ),
        ),
      ),
    );
  }

  Widget _infoItem(IconData icon, String label, String value) {
    return Row(
      children: [
        Container(
          width: 4,
          height: 32,
          decoration: BoxDecoration(
            color: const Color(0xFF006C4A),
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: GoogleFonts.inter(color: const Color(0xFF747686), fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.5)),
              Text(value, style: GoogleFonts.plusJakartaSans(color: const Color(0xFF111C2D), fontSize: 15, fontWeight: FontWeight.bold)),
            ],
          ),
        ),
        Icon(icon, color: const Color(0xFFC4C5D7), size: 22),
      ],
    );
  }

  Widget _infoDetail(String label, String value, {String? subValue}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: GoogleFonts.inter(color: const Color(0xFF747686), fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.5)),
        const SizedBox(height: 4),
        Text(value, style: GoogleFonts.plusJakartaSans(color: const Color(0xFF111C2D), fontSize: 14, fontWeight: FontWeight.w700)),
        if (subValue != null)
          Text(subValue, style: GoogleFonts.inter(color: const Color(0xFF006C4A), fontSize: 11, fontWeight: FontWeight.bold)),
      ],
    );
  }

  Widget _contactItem(IconData icon, String label, String value) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(color: const Color(0xFFF0F3FF), borderRadius: BorderRadius.circular(10)),
          child: Icon(icon, color: const Color(0xFF0037B0), size: 18),
        ),
        const SizedBox(width: 16),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: GoogleFonts.inter(color: const Color(0xFF747686), fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.5)),
            Text(value, style: GoogleFonts.jetBrainsMono(color: const Color(0xFF111C2D), fontSize: 14, fontWeight: FontWeight.w600)),
          ],
        ),
      ],
    );
  }

  Widget _buildSettingsSection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionHeader('KEAMANAN'),
          _settingsGroup([
            _settingsTile(Icons.lock_outline, 'Ganti PIN', onTap: () {}),
            _settingsToggle(Icons.fingerprint, 'Biometrik', 'Face ID / Fingerprint', _biometricEnabled, (v) => setState(() => _biometricEnabled = v)),
            _settingsToggle(Icons.notifications_none, 'Notifikasi Push', null, _notificationEnabled, (v) => setState(() => _notificationEnabled = v)),
          ]),
          const SizedBox(height: 32),
          _sectionHeader('INFORMASI'),
          _settingsGroup([
            _settingsTile(Icons.info_outline, 'Tentang KoperasiKu', iconColor: const Color(0xFF825100)),
            _settingsTile(Icons.description_outlined, 'Syarat & Ketentuan', iconColor: const Color(0xFF825100)),
            _settingsTile(Icons.support_agent, 'Hubungi Koperasi', iconColor: const Color(0xFF825100)),
          ]),
          const SizedBox(height: 32),
          _sectionHeader('AKUN'),
          _settingsGroup([
            _settingsTile(Icons.logout, 'Keluar dari Aplikasi', iconColor: Colors.red.shade600, textColor: Colors.red.shade600, showArrow: false, onTap: _handleLogout),
          ]),
        ],
      ),
    );
  }

  Widget _sectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 12),
      child: Text(
        title,
        style: GoogleFonts.inter(color: const Color(0xFF747686), fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 1.2),
      ),
    );
  }

  Widget _settingsGroup(List<Widget> children) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFF9F9FF),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFF0F3FF)),
      ),
      child: Column(children: children),
    );
  }

  Widget _settingsTile(IconData icon, String title, {Color? iconColor, Color? textColor, bool showArrow = true, VoidCallback? onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        decoration: const BoxDecoration(
          border: Border(bottom: BorderSide(color: Color(0xFFF0F3FF), width: 1)),
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: (iconColor ?? const Color(0xFF0037B0)).withOpacity(0.1), 
                borderRadius: BorderRadius.circular(12)
              ),
              child: Icon(icon, color: iconColor ?? const Color(0xFF0037B0), size: 20),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Text(title, style: GoogleFonts.inter(color: textColor ?? const Color(0xFF111C2D), fontSize: 15, fontWeight: FontWeight.w600)),
            ),
            if (showArrow)
              const Icon(Icons.chevron_right, color: Color(0xFFC4C5D7), size: 20),
          ],
        ),
      ),
    );
  }

  Widget _settingsToggle(IconData icon, String title, String? subtitle, bool value, ValueChanged<bool> onChanged) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Color(0xFFF0F3FF), width: 1)),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(color: const Color(0xFF0037B0).withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
            child: Icon(icon, color: const Color(0xFF0037B0), size: 20),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: GoogleFonts.inter(color: const Color(0xFF111C2D), fontSize: 15, fontWeight: FontWeight.w600)),
                if (subtitle != null)
                  Text(subtitle, style: GoogleFonts.inter(color: const Color(0xFF747686), fontSize: 11)),
              ],
            ),
          ),
          Switch(
            value: value,
            onChanged: onChanged,
            activeColor: const Color(0xFF006C4A),
          ),
        ],
      ),
    );
  }
}
