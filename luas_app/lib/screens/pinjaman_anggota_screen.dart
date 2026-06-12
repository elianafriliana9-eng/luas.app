import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class PinjamanAnggotaScreen extends StatefulWidget {
  final Map<String, dynamic>? dashboardData;

  const PinjamanAnggotaScreen({super.key, this.dashboardData});

  @override
  State<PinjamanAnggotaScreen> createState() => _PinjamanAnggotaScreenState();
}

class _PinjamanAnggotaScreenState extends State<PinjamanAnggotaScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _pinjamanDetail;

  static const Color primary = Color(0xFFF97316); // Orange-500
  static const Color primaryLight = Color(0xFFFB923C); // Orange-400
  static const Color surface = Color(0xFFF8FAFC);

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    final result = await ApiService.getPembiayaanDetail();
    if (mounted) {
      if (result['success']) {
        setState(() {
          _pinjamanDetail = result['data'];
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        // It's possible the user has no loan, so it's not strictly an error.
      }
    }
  }

  String _formatCurrency(dynamic value) {
    if (value == null) return '0';
    final number = double.tryParse(value.toString()) ?? 0;
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: '',
      decimalDigits: 0,
    ).format(number).trim();
  }

  @override
  Widget build(BuildContext context) {
    final totalTagihan = widget.dashboardData?['total_tagihan'] ?? 0;

    return Scaffold(
      backgroundColor: surface,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: Color(0xFF0F172A)),
        title: Text(
          'Pinjaman Anggota',
          style: GoogleFonts.plusJakartaSans(
            color: const Color(0xFF0F172A),
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Saldo Card
            Container(
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
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Total Sisa Tagihan',
                        style: GoogleFonts.inter(
                          color: Colors.white.withValues(alpha: 0.9),
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const Icon(Icons.credit_score_rounded, color: Colors.white, size: 24),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Rp ${_formatCurrency(totalTagihan)}',
                    style: GoogleFonts.jetBrainsMono(
                      color: Colors.white,
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      letterSpacing: -1,
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (_pinjamanDetail != null) ...[
                    Text(
                      'No. Pembiayaan: ${_pinjamanDetail!['no_pembiayaan'] ?? '-'}',
                      style: GoogleFonts.inter(
                        color: Colors.white.withValues(alpha: 0.8),
                        fontSize: 12,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Plafon Awal: Rp ${_formatCurrency(_pinjamanDetail!['plafon'])}',
                      style: GoogleFonts.inter(
                        color: Colors.white.withValues(alpha: 0.8),
                        fontSize: 12,
                      ),
                    ),
                  ] else ...[
                    Text(
                      'Anda tidak memiliki pinjaman aktif saat ini.',
                      style: GoogleFonts.inter(
                        color: Colors.white.withValues(alpha: 0.8),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ],
              ),
            ),
            
            const SizedBox(height: 32),
            Text(
              'Jadwal Angsuran',
              style: GoogleFonts.plusJakartaSans(
                color: const Color(0xFF0F172A),
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            
            if (_isLoading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(32.0),
                  child: CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(primary),
                  ),
                ),
              )
            else if (_pinjamanDetail == null || _pinjamanDetail!['installments'] == null || (_pinjamanDetail!['installments'] as List).isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(32.0),
                  child: Column(
                    children: [
                      const Icon(Icons.check_circle_outline_rounded, size: 64, color: Color(0xFF10B981)),
                      const SizedBox(height: 16),
                      Text(
                        'Tidak ada tagihan',
                        textAlign: TextAlign.center,
                        style: GoogleFonts.inter(
                          color: const Color(0xFF64748B),
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: (_pinjamanDetail!['installments'] as List).length,
                separatorBuilder: (_, __) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final inst = _pinjamanDetail!['installments'][index];
                  final isLunas = inst['status'] == 'lunas';
                  final isCurrent = inst['is_current'] == true;
                  
                  return Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: isCurrent ? primary : const Color(0xFFF1F5F9),
                        width: isCurrent ? 2 : 1,
                      ),
                      boxShadow: [
                        if (isCurrent)
                          BoxShadow(
                            color: primary.withValues(alpha: 0.1),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          )
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: isLunas ? const Color(0xFFECFDF5) : const Color(0xFFF1F5F9),
                            shape: BoxShape.circle,
                          ),
                          child: Center(
                            child: Text(
                              '${inst['ke']}',
                              style: GoogleFonts.plusJakartaSans(
                                color: isLunas ? const Color(0xFF059669) : const Color(0xFF64748B),
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Jatuh Tempo',
                                style: GoogleFonts.inter(
                                  color: const Color(0xFF64748B),
                                  fontSize: 12,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                inst['tanggal_jatuh_tempo'] ?? '-',
                                style: GoogleFonts.inter(
                                  color: const Color(0xFF0F172A),
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              'Rp ${_formatCurrency(inst['nominal'])}',
                              style: GoogleFonts.jetBrainsMono(
                                color: const Color(0xFF0F172A),
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: isLunas ? const Color(0xFFD1FAE5) : const Color(0xFFFEF2F2),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                isLunas ? 'LUNAS' : 'BELUM',
                                style: GoogleFonts.inter(
                                  color: isLunas ? const Color(0xFF059669) : const Color(0xFFDC2626),
                                  fontSize: 10,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                },
              ),
          ],
        ),
      ),
    );
  }
}
