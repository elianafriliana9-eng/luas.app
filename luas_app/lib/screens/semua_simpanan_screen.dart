import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import 'simpanan_pokok_screen.dart';
import 'simpanan_sukarela_screen.dart';
import 'simpanan_wajib_screen.dart';

class SemuaSimpananScreen extends StatefulWidget {
  const SemuaSimpananScreen({super.key});

  @override
  State<SemuaSimpananScreen> createState() => _SemuaSimpananScreenState();
}

class _SemuaSimpananScreenState extends State<SemuaSimpananScreen> {
  bool _isLoading = true;
  List<dynamic> _rekenings = [];

  static const Color primary = Color(0xFF0D47A1);
  static const Color surface = Color(0xFFF8FAFC);
  static const Color textPrimary = Color(0xFF1E293B);

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    final result = await ApiService.getDashboard();
    if (result['success'] && mounted) {
      setState(() {
        _rekenings = result['data']['rekening'] ?? [];
        _isLoading = false;
      });
    } else {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  String _formatCurrency(dynamic value) {
    if (value == null) return 'Rp 0';
    final number = double.tryParse(value.toString()) ?? 0;
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    ).format(number).trim();
  }

  IconData _getIconForProduk(String produk) {
    final lower = produk.toLowerCase();
    if (lower.contains('pokok')) return Icons.account_balance_wallet_outlined;
    if (lower.contains('wajib')) return Icons.savings_outlined;
    if (lower.contains('sukarela')) return Icons.volunteer_activism_outlined;
    return Icons.account_balance_rounded;
  }

  Color _getColorForProduk(String produk) {
    final lower = produk.toLowerCase();
    if (lower.contains('pokok')) return primary;
    if (lower.contains('wajib')) return const Color(0xFF059669);
    if (lower.contains('sukarela')) return const Color(0xFFF59E0B);
    return Colors.grey.shade700;
  }

  void _routeToDetail(String produk) {
    final lower = produk.toLowerCase();
    if (lower.contains('pokok')) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => const SimpananPokokScreen()));
    } else if (lower.contains('wajib')) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => const SimpananWajibScreen()));
    } else if (lower.contains('sukarela')) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => const SimpananSukarelaScreen()));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: surface,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: textPrimary),
        title: Text(
          'Semua Simpanan',
          style: GoogleFonts.poppins(
            color: textPrimary,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation<Color>(primary)))
          : _rekenings.isEmpty
              ? Center(
                  child: Text(
                    'Belum ada data simpanan',
                    style: GoogleFonts.poppins(color: Colors.grey),
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(24),
                  itemCount: _rekenings.length,
                  itemBuilder: (context, index) {
                    final rek = _rekenings[index];
                    final produkName = rek['produk'] ?? 'Simpanan';
                    final noRekening = rek['no_rekening'] ?? '-';
                    final saldo = rek['saldo'] ?? 0;
                    final iconColor = _getColorForProduk(produkName);

                    return Container(
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.04),
                            blurRadius: 20,
                            offset: const Offset(0, 10),
                          ),
                        ],
                      ),
                      child: InkWell(
                        onTap: () => _routeToDetail(produkName),
                        borderRadius: BorderRadius.circular(20),
                        child: Padding(
                          padding: const EdgeInsets.all(20),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: iconColor.withValues(alpha: 0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  _getIconForProduk(produkName),
                                  color: iconColor,
                                  size: 28,
                                ),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      produkName,
                                      style: GoogleFonts.poppins(
                                        color: textPrimary,
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Rek: $noRekening',
                                      style: GoogleFonts.poppins(
                                        color: Colors.grey.shade600,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Text(
                                _formatCurrency(saldo),
                                style: GoogleFonts.poppins(
                                  color: textPrimary,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
