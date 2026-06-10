import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class PayLaterScreen extends StatefulWidget {
  const PayLaterScreen({super.key});

  @override
  State<PayLaterScreen> createState() => _PayLaterScreenState();
}

class _PayLaterScreenState extends State<PayLaterScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _paymentData;
  String _errorMessage = '';
  bool _isSubmitting = false;

  static const Color primaryBlue = Color(0xFF0037B0);
  static const Color primaryContainer = Color(0xFF1D4ED8);
  static const Color secondaryGreen = Color(0xFF006C4A);
  static const Color surfaceColor = Color(0xFFF9F9FF);
  static const Color onSurfaceVariant = Color(0xFF434655);

  @override
  void initState() {
    super.initState();
    _fetchPaymentDetail();
  }

  Future<void> _fetchPaymentDetail() async {
    final result = await ApiService.getPaymentDetail();
    if (result['success']) {
      setState(() {
        _paymentData = result['data'];
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = result['message'] ?? 'Gagal mengambil data';
        _isLoading = false;
      });
    }
  }

  Future<void> _submitPayLater(Map<String, dynamic> installment) async {
    setState(() => _isSubmitting = true);

    final result = await ApiService.submitPayLater(
      pembiayaanId: _paymentData?['no_pembiayaan'] ?? '',
      jadwalAngsuranId: installment['id'],
      nominal: double.parse(installment['nominal'].toString()),
      jenis: 'angsuran',
      keterangan: 'Pembayaran angsuran ke-${installment['ke']} sebelum tanggal gajian',
    );

    setState(() => _isSubmitting = false);

    if (!mounted) return;

    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Pengajuan berhasil'),
          backgroundColor: secondaryGreen,
        ),
      );
      _fetchPaymentDetail();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Gagal mengajukan'),
          backgroundColor: Colors.red.shade700,
        ),
      );
    }
  }

  final currencyFormat = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: primaryContainer)),
      );
    }

    if (_errorMessage.isNotEmpty) {
      return Scaffold(
        backgroundColor: surfaceColor,
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: primaryBlue),
            onPressed: () => Navigator.pop(context),
          ),
          title: Text('Bayar Sebelum Gajian', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold)),
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.info_outline, size: 64, color: Color(0xFF747686)),
                const SizedBox(height: 16),
                Text(
                  _errorMessage == 'No active loan found.'
                      ? 'Anda tidak memiliki pinjaman aktif saat ini.'
                      : _errorMessage,
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(fontSize: 16, color: const Color(0xFF434655)),
                ),
              ],
            ),
          ),
        ),
      );
    }

    final data = _paymentData!;
    final installments = data['installments'] as List;
    final unpaidInstallments = installments.where((i) => i['status'] == 'belum').toList();
    final autoPotongGaji = data['auto_potong_gaji'] ?? false;
    final sumberPembayaran = data['sumber_pembayaran'] ?? 'bayar_manual';

    return Scaffold(
      backgroundColor: surfaceColor,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: primaryBlue),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Bayar Sebelum Gajian',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF111C2D),
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Info card
            _buildInfoCard(autoPotongGaji, sumberPembayaran),
            const SizedBox(height: 24),

            Text(
              'ANGSURAN YANG BELUM DIBAYAR',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF434655),
                letterSpacing: 1.2,
              ),
            ),
            const SizedBox(height: 16),

            if (unpaidInstallments.isEmpty)
              Center(
                child: Column(
                  children: [
                    const Icon(Icons.check_circle_outline, size: 64, color: secondaryGreen),
                    const SizedBox(height: 16),
                    Text(
                      'Semua angsuran sudah lunas!',
                      style: GoogleFonts.inter(fontSize: 16, color: const Color(0xFF434655)),
                    ),
                  ],
                ),
              )
            else
              ...unpaidInstallments.map((item) => _buildPayLaterTile(item)).toList(),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoCard(bool autoPotongGaji, String sumberPembayaran) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFDCE1FF),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: primaryBlue.withOpacity(0.2)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.info_outline, color: primaryBlue, size: 24),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Bayar Sebelum Gajian',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: primaryBlue,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  autoPotongGaji
                      ? 'Angsuran Anda otomatis dipotong saat gajian. Gunakan fitur ini jika ingin bayar lebih awal.'
                      : 'Anda bisa membayar angsuran sebelum tanggal gajian.',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: onSurfaceVariant,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPayLaterTile(Map<String, dynamic> item) {
    final viaPotongGaji = item['via_potong_gaji'] ?? false;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: const Color(0xFFDCE1FF),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.calendar_today,
                      color: primaryBlue,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Angsuran ke-${item['ke']}',
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF111C2D),
                        ),
                      ),
                      Text(
                        'Jatuh Tempo: ${item['tanggal_jatuh_tempo']}',
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          color: const Color(0xFF434655),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    currencyFormat.format(double.parse(item['nominal'].toString())),
                    style: GoogleFonts.jetBrainsMono(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: primaryBlue,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (viaPotongGaji)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFF006C4A).withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.check_circle, color: Color(0xFF006C4A), size: 14),
                  const SizedBox(width: 4),
                  Text(
                    'Akan dipotong dari gaji',
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF006C4A),
                    ),
                  ),
                ],
              ),
            )
          else
            SizedBox(
              width: double.infinity,
              height: 40,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : () => _showConfirmDialog(item),
                style: ElevatedButton.styleFrom(
                  backgroundColor: secondaryGreen,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : Text(
                        'Bayar Sekarang',
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),
        ],
      ),
    );
  }

  void _showConfirmDialog(Map<String, dynamic> installment) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Konfirmasi Pembayaran', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Angsuran ke-${installment['ke']}',
              style: GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              currencyFormat.format(double.parse(installment['nominal'].toString())),
              style: GoogleFonts.jetBrainsMono(fontSize: 18, fontWeight: FontWeight.w800, color: primaryBlue),
            ),
            const SizedBox(height: 12),
            Text(
              'Pembayaran ini akan diproses di luar jadwal potong gaji. Apakah Anda yakin?',
              style: GoogleFonts.inter(fontSize: 13, color: onSurfaceVariant),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Batal', style: GoogleFonts.inter(color: Colors.grey, fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _submitPayLater(installment);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: secondaryGreen,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: Text('Ya, Bayar', style: GoogleFonts.inter(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}
