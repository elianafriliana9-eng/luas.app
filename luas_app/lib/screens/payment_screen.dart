import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class PaymentScreen extends StatefulWidget {
  final bool isTab;

  const PaymentScreen({super.key, this.isTab = false});

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _paymentData;
  String _errorMessage = '';

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

  final currencyFormat = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (_errorMessage.isNotEmpty) {
      return Scaffold(
        backgroundColor: const Color(0xFFF9F9FF),
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          automaticallyImplyLeading: !widget.isTab,
          leading: widget.isTab 
              ? null 
              : IconButton(
                  icon: const Icon(Icons.arrow_back, color: Color(0xFF0037B0)),
                  onPressed: () => Navigator.pop(context),
                ),
          title: Text('Bayar Angsuran', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold)),
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
    final progress = data['progress'];
    final installments = data['installments'] as List;
    final currentInstallment = installments.firstWhere((i) => i['is_current'] == true, orElse: () => null);

    return Scaffold(
      backgroundColor: const Color(0xFFF9F9FF),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        automaticallyImplyLeading: !widget.isTab,
        leading: widget.isTab 
            ? null 
            : IconButton(
                icon: const Icon(Icons.arrow_back, color: Color(0xFF0037B0)),
                onPressed: () => Navigator.pop(context),
              ),
        title: Text(
          'Bayar Angsuran',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF111C2D),
          ),
        ),
      ),
      body: Stack(
        children: [
          SingleChildScrollView(
            padding: const EdgeInsets.only(left: 20, right: 20, top: 24, bottom: 120),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Loan Summary Card
                _buildLoanSummary(data, progress),
                
                const SizedBox(height: 24),
                
                Text(
                  'DAFTAR TAGIHAN',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF434655),
                    letterSpacing: 1.2,
                  ),
                ),
                
                const SizedBox(height: 16),
                
                // List of Installments
                ...installments.map((item) => _buildInstallmentTile(item)).toList(),
                
                const SizedBox(height: 16),
                
                // Decorative Image Placeholder (Simulated blueprint opacity)
                _buildFooterIllustration(),
              ],
            ),
          ),
          
          // Bottom Payment Bar
          if (currentInstallment != null)
            _buildBottomActionPanel(currentInstallment),
        ],
      ),
    );
  }

  Widget _buildLoanSummary(Map<String, dynamic> data, Map<String, dynamic> progress) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: const Border(
          left: BorderSide(color: Color(0xFF0037B0), width: 4),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
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
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'PRODUK PINJAMAN',
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF434655),
                      letterSpacing: 1,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    data['produk'] ?? 'Pembiayaan Reguler',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF111C2D),
                    ),
                  ),
                ],
              ),
              const Icon(Icons.verified_user, color: Color(0xFF0037B0), size: 24),
            ],
          ),
          const SizedBox(height: 24),
          Text(
            'TOTAL PLAFON',
            style: GoogleFonts.inter(
              fontSize: 10,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF434655),
              letterSpacing: 1,
            ),
          ),
          const SizedBox(height: 4),
          RichText(
            text: TextSpan(
              children: [
                TextSpan(
                  text: 'Rp ',
                  style: GoogleFonts.jetBrainsMono(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF0037B0),
                  ),
                ),
                TextSpan(
                  text: NumberFormat('#,###', 'id_ID').format(double.parse(data['plafon'].toString())),
                  style: GoogleFonts.jetBrainsMono(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF0037B0),
                    letterSpacing: -1,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Progress Pelunasan',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: const Color(0xFF434655),
                ),
              ),
              Text(
                '${progress['current']} dari ${progress['total']} Bulan',
                style: GoogleFonts.jetBrainsMono(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: const Color(0xFF434655),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: LinearProgressIndicator(
              value: double.parse(progress['percent'].toString()) / 100,
              minHeight: 12,
              backgroundColor: const Color(0xFFE7EEFF),
              valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF0037B0)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInstallmentTile(Map<String, dynamic> item) {
    final isCurrent = item['is_current'] == true;
    final isLunas = item['status'] == 'lunas';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isCurrent 
            ? const Color(0xFFDCE1FF).withOpacity(0.3) 
            : const Color(0xFFF0F3FF),
        borderRadius: BorderRadius.circular(16),
        border: isCurrent 
            ? Border.all(color: const Color(0xFF0037B0), width: 2) 
            : Border.all(color: Colors.transparent),
      ),
      child: Opacity(
        opacity: (isCurrent || isLunas) ? 1.0 : 0.6,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: isLunas 
                        ? const Color(0xFF006C4A) 
                        : (isCurrent ? const Color(0xFF0037B0) : const Color(0xFFC4C5D7)),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    isLunas ? Icons.check_circle : (isCurrent ? Icons.check_circle : Icons.radio_button_unchecked),
                    color: Colors.white,
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
                    color: isCurrent ? const Color(0xFF0037B0) : const Color(0xFF111C2D),
                  ),
                ),
                if (isCurrent)
                  Container(
                    margin: const EdgeInsets.only(top: 4),
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFDDB8).withOpacity(0.5),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      'JATUH TEMPO',
                      style: GoogleFonts.inter(
                        fontSize: 8,
                        fontWeight: FontWeight.w900,
                        color: const Color(0xFF623C00),
                      ),
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFooterIllustration() {
    return Container(
      margin: const EdgeInsets.only(top: 16),
      height: 100,
      width: double.infinity,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        image: const DecorationImage(
          image: NetworkImage('https://lh3.googleusercontent.com/aida-public/AB6AXuBnF4iBj_57C71rgl5Gt5bP7TI8yr7zVDFv-T9RMYh8cCtjRcF48cA4AbgLsSbvjdBPEzF2WrwXTgRKznur1YLAQN5o2yiC2AMnndHtA2h4m_9BVUyLGsb34ztsxDKYBX1tNscvk2Nkv-R0DjVwvh-jpZyHyw8XYLO4QDt4gpnbkmjwxdcfRGgybQL33Jg0HqFOdmleb5BSNtvcIiDSh9-5nZoC0dpWbHmxNlf0cl5zUB98mKsMeuvn-rPiYIpv_zK4j0b4YwRker8'),
          fit: BoxFit.cover,
          colorFilter: ColorFilter.mode(Colors.white, BlendMode.softLight),
        ),
      ),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.transparent, const Color(0xFFF0F3FF).withOpacity(0.8)],
          ),
        ),
        padding: const EdgeInsets.all(16),
        alignment: Alignment.bottomLeft,
        child: Text(
          '"Membangun masa depan yang lebih baik\nbersama Koperasi Mandala Utama."',
          style: GoogleFonts.inter(
            fontSize: 10,
            fontStyle: FontStyle.italic,
            fontWeight: FontWeight.w500,
            color: const Color(0xFF434655),
          ),
        ),
      ),
    );
  }

  Widget _buildBottomActionPanel(Map<String, dynamic> currentInstallment) {
    return Positioned(
      bottom: 0,
      left: 0,
      right: 0,
      child: Container(
        padding: const EdgeInsets.only(left: 24, right: 24, top: 20, bottom: 40),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.9),
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(24),
            topRight: Radius.circular(24),
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0037B0).withOpacity(0.08),
              blurRadius: 24,
              offset: const Offset(0, -8),
            ),
          ],
        ),
        child: Column(
          children: [
            // Pay Before Salary button
            SizedBox(
              width: double.infinity,
              height: 48,
              child: OutlinedButton.icon(
                onPressed: () {
                  Navigator.pushNamed(context, '/pay-later');
                },
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: Color(0xFF006C4A), width: 1.5),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
                icon: const Icon(Icons.calendar_month, color: Color(0xFF006C4A), size: 20),
                label: Text(
                  'Bayar Sebelum Gajian',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF006C4A),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Total Pembayaran',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: const Color(0xFF434655),
                  ),
                ),
                Text(
                  currencyFormat.format(double.parse(currentInstallment['nominal'].toString())),
                  style: GoogleFonts.jetBrainsMono(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    color: const Color(0xFF0037B0),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pushNamed(
                    context,
                    '/payment-method',
                    arguments: {'amount': double.parse(currentInstallment['nominal'].toString())}
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF006C4A),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  elevation: 0,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Bayar Sekarang',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Icon(Icons.arrow_forward, color: Colors.white, size: 20),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
