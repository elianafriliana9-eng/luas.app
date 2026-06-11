import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/api_service.dart';

class DataAnggotaScreen extends StatefulWidget {
  const DataAnggotaScreen({super.key});

  @override
  State<DataAnggotaScreen> createState() => _DataAnggotaScreenState();
}

class _DataAnggotaScreenState extends State<DataAnggotaScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _profileData;

  static const Color primary = Color(0xFF7C3AED); // Violet-600
  static const Color primaryLight = Color(0xFF8B5CF6); // Violet-500
  static const Color surface = Color(0xFFF8FAFC);

  @override
  void initState() {
    super.initState();
    _fetchProfile();
  }

  Future<void> _fetchProfile() async {
    final result = await ApiService.getProfile();
    if (mounted) {
      if (result['success']) {
        setState(() {
          _profileData = result['data'];
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Gagal memuat profil')),
        );
      }
    }
  }

  Widget _buildInfoRow(String label, String value, {bool isHighlighted = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 2,
            child: Text(
              label,
              style: GoogleFonts.inter(
                color: const Color(0xFF64748B),
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            flex: 3,
            child: Text(
              value,
              style: GoogleFonts.inter(
                color: isHighlighted ? primary : const Color(0xFF0F172A),
                fontSize: 13,
                fontWeight: isHighlighted ? FontWeight.bold : FontWeight.w600,
              ),
              textAlign: TextAlign.right,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: surface,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: Color(0xFF0F172A)),
        title: Text(
          'Data Anggota',
          style: GoogleFonts.plusJakartaSans(
            color: const Color(0xFF0F172A),
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(primary),
              ),
            )
          : _profileData == null
              ? Center(
                  child: Text(
                    'Data tidak ditemukan',
                    style: GoogleFonts.inter(color: Colors.grey),
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      // Header Card
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [primary, primaryLight],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: [
                            BoxShadow(
                              color: primary.withValues(alpha: 0.3),
                              blurRadius: 20,
                              offset: const Offset(0, 10),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            Container(
                              width: 80,
                              height: 80,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: Colors.white,
                                border: Border.all(color: Colors.white, width: 3),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.1),
                                    blurRadius: 10,
                                    offset: const Offset(0, 5),
                                  ),
                                ],
                              ),
                              child: Center(
                                child: Text(
                                  _profileData?['inisial'] ?? '??',
                                  style: GoogleFonts.plusJakartaSans(
                                    color: primary,
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              _profileData?['nama_lengkap'] ?? '-',
                              style: GoogleFonts.plusJakartaSans(
                                color: Colors.white,
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                _profileData?['status'] ?? 'Aktif',
                                style: GoogleFonts.inter(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 32),

                      // Identitas Koperasi
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFF1F5F9)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.02),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Identitas Koperasi',
                              style: GoogleFonts.plusJakartaSans(
                                color: const Color(0xFF0F172A),
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const Divider(height: 24),
                            _buildInfoRow('No. Anggota', _profileData?['no_anggota'] ?? '-', isHighlighted: true),
                            _buildInfoRow('Tanggal Masuk', _profileData?['tanggal_masuk'] ?? '-'),
                            _buildInfoRow('Masa Keanggotaan', _profileData?['durasi'] ?? '-'),
                            _buildInfoRow('Cabang', _profileData?['cabang'] ?? '-'),
                          ],
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Data Pekerjaan
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFF1F5F9)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.02),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Data Kepegawaian',
                              style: GoogleFonts.plusJakartaSans(
                                color: const Color(0xFF0F172A),
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const Divider(height: 24),
                            _buildInfoRow('NIK', _profileData?['nik'] ?? '-'),
                            _buildInfoRow('No. Pegawai', _profileData?['no_pegawai'] ?? '-'),
                            _buildInfoRow('Departemen', _profileData?['departemen'] ?? '-'),
                            _buildInfoRow('Jabatan', _profileData?['jabatan'] ?? '-'),
                            _buildInfoRow('Masa Kerja', _profileData?['masa_kerja'] ?? '-'),
                          ],
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Kontak
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFF1F5F9)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.02),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Kontak Pribadi',
                              style: GoogleFonts.plusJakartaSans(
                                color: const Color(0xFF0F172A),
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const Divider(height: 24),
                            _buildInfoRow('Email', _profileData?['email'] ?? '-'),
                            _buildInfoRow('No. Handphone', _profileData?['no_hp'] ?? '-'),
                          ],
                        ),
                      ),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
    );
  }
}
