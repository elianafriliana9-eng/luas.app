import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

class SimpananPokokScreen extends StatelessWidget {
  final Map<String, dynamic>? dashboardData;

  const SimpananPokokScreen({super.key, this.dashboardData});

  static const Color primary = Color(0xFF1D4ED8);
  static const Color surface = Color(0xFFF8FAFC);
  static const double targetPokok = 150000.0;

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
    // Cari rekening Simpanan Pokok
    Map<String, dynamic>? rekeningPokok;
    if (dashboardData != null && dashboardData!['rekening'] != null) {
      for (var rek in dashboardData!['rekening']) {
        if ((rek['produk'] as String).toLowerCase().contains('pokok')) {
          rekeningPokok = rek;
          break;
        }
      }
    }

    final double saldo = double.tryParse(rekeningPokok?['saldo']?.toString() ?? '0') ?? 0;
    final double progress = (saldo / targetPokok).clamp(0.0, 1.0);

    // Filter transaksi Simpanan Pokok
    List<dynamic> history = [];
    if (dashboardData != null && dashboardData!['recent_transactions'] != null) {
      for (var trx in dashboardData!['recent_transactions']) {
        if ((trx['produk'] as String).toLowerCase().contains('pokok')) {
          history.add(trx);
        }
      }
    }

    return Scaffold(
      backgroundColor: surface,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: Color(0xFF0F172A)),
        title: Text(
          'Simpanan Pokok',
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
                  colors: [primary, Color(0xFF3B82F6)],
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
                        'Total Saldo Pokok',
                        style: GoogleFonts.inter(
                          color: Colors.white.withValues(alpha: 0.8),
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const Icon(Icons.account_balance_wallet_rounded, color: Colors.white, size: 24),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Rp ${_formatCurrency(saldo)}',
                    style: GoogleFonts.jetBrainsMono(
                      color: Colors.white,
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      letterSpacing: -1,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Progres Pemenuhan',
                        style: GoogleFonts.inter(
                          color: Colors.white.withValues(alpha: 0.8),
                          fontSize: 12,
                        ),
                      ),
                      Text(
                        '${(progress * 100).toInt()}%',
                        style: GoogleFonts.inter(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: progress,
                      backgroundColor: Colors.white.withValues(alpha: 0.2),
                      valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
                      minHeight: 8,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Target: Rp ${_formatCurrency(targetPokok)}',
                    style: GoogleFonts.inter(
                      color: Colors.white.withValues(alpha: 0.8),
                      fontSize: 10,
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 32),
            Text(
              'Riwayat Transaksi (Pokok)',
              style: GoogleFonts.plusJakartaSans(
                color: const Color(0xFF0F172A),
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            
            if (history.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(32.0),
                  child: Column(
                    children: [
                      Icon(Icons.history_rounded, size: 64, color: const Color(0xFFCBD5E1)),
                      const SizedBox(height: 16),
                      Text(
                        'Belum ada transaksi simpanan pokok',
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
                itemCount: history.length,
                separatorBuilder: (_, __) => const Divider(height: 32),
                itemBuilder: (context, index) {
                  final trx = history[index];
                  final isDebit = trx['is_debit'] ?? false;
                  
                  return Row(
                    children: [
                      Container(
                        width: 48,
                        height: 48,
                        decoration: BoxDecoration(
                          color: isDebit ? const Color(0xFFFEE2E2) : const Color(0xFFECFDF5),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          isDebit ? Icons.arrow_outward_rounded : Icons.south_west_rounded,
                          color: isDebit ? const Color(0xFFDC2626) : const Color(0xFF059669),
                          size: 24,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              trx['keterangan'] ?? 'Transaksi',
                              style: GoogleFonts.inter(
                                color: const Color(0xFF0F172A),
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              trx['tanggal'],
                              style: GoogleFonts.inter(
                                color: const Color(0xFF64748B),
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Text(
                        '${isDebit ? '-' : '+'} Rp ${_formatCurrency(trx['nominal'])}',
                        style: GoogleFonts.jetBrainsMono(
                          color: isDebit ? const Color(0xFFDC2626) : const Color(0xFF059669),
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  );
                },
              ),
          ],
        ),
      ),
    );
  }
}
