import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

class PaymentMethodScreen extends StatefulWidget {
  final double amount;

  const PaymentMethodScreen({super.key, required this.amount});

  @override
  State<PaymentMethodScreen> createState() => _PaymentMethodScreenState();
}

class _PaymentMethodScreenState extends State<PaymentMethodScreen> {
  String _selectedMethod = 'qris';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9F9FF),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF0037B0)),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Pilih Metode Pembayaran',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF111C2D),
          ),
        ),
      ),
      body: Column(
        children: [
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                   Text(
                    'METODE PEMBAYARAN',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF434655),
                      letterSpacing: 1.2,
                    ),
                  ),
                  const SizedBox(height: 16),
                  
                  // QRIS Option
                  _buildPaymentOption(
                    id: 'qris',
                    title: 'QRIS',
                    subtitle: 'Scan QR Code apa saja',
                    icon: Icons.qr_code_2,
                    isRecommended: true,
                  ),
                  
                  // BRI VA Option
                  _buildPaymentOption(
                    id: 'bri_va',
                    title: 'Virtual Account BRI',
                    subtitle: '8801 0812 3456 7890',
                    isImage: true,
                    imageUrl: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCrJf6fSABiBlVe0ezF9tf1eqtoDt1pbudrBYW8o1q1KdFf6H1LFviAXzF2XKa6qaK9RRem5yqJbWDpazycBr4rFiPRnbRNMDAtNasvt7pyKlRFVvfvlYHf927r26e5wbT8OyXd4XbBJmLT0muSvIsdWKjpltf--aQsz4qsxzzdTHyTJgJRmtBjuw_91GS6By7YMJR0zhDOEu3HYW2Up4_3sfDm07ex7euGE70DkOxkKrN2QXNBitTBKa6C_m4rYZieCCLs1XZpCak',
                  ),

                  // Transfer Rekening Option
                  _buildPaymentOption(
                    id: 'transfer',
                    title: 'Transfer Rekening',
                    subtitle: 'Transfer dari saldo simpanan Anda',
                    icon: Icons.swap_horiz_rounded,
                  ),
                  
                  // Cashier Option
                  _buildPaymentOption(
                    id: 'cashier',
                    title: 'Bayar di Kasir',
                    subtitle: 'Kunjungi gerai koperasi terdekat',
                    icon: Icons.storefront,
                  ),
                  
                  const SizedBox(height: 24),
                  
                  // Status Card (Gradient)
                  _buildStatusCard(),
                ],
              ),
            ),
          ),
          
          // Bottom Action Bar
          _buildBottomPanel(),
        ],
      ),
    );
  }

  Widget _buildPaymentOption({
    required String id,
    required String title,
    required String subtitle,
    IconData? icon,
    bool isRecommended = false,
    bool isImage = false,
    String? imageUrl,
  }) {
    bool isSelected = _selectedMethod == id;

    return GestureDetector(
      onTap: () => setState(() => _selectedMethod = id),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? const Color(0xFF0037B0) : Colors.transparent,
            width: 2,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: const Color(0xFFF0F3FF),
                borderRadius: BorderRadius.circular(12),
              ),
              child: isImage 
                ? Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Image.network(
                      imageUrl!, 
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) => const Icon(Icons.account_balance, color: Color(0xFF0037B0)),
                    ),
                  )
                : Icon(icon, color: const Color(0xFF0037B0), size: 28),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        title,
                        style: GoogleFonts.inter(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF111C2D),
                        ),
                      ),
                      if (isRecommended) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFF82F5C1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'REKOMENDASI',
                            style: GoogleFonts.inter(
                              fontSize: 8,
                              fontWeight: FontWeight.w900,
                              color: const Color(0xFF002114),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  Text(
                    subtitle,
                    style: id == 'bri_va' 
                      ? GoogleFonts.jetBrainsMono(fontSize: 11, color: const Color(0xFF434655))
                      : GoogleFonts.inter(fontSize: 11, color: const Color(0xFF434655)),
                  ),
                ],
              ),
            ),
            Radio<String>(
              value: id,
              groupValue: _selectedMethod,
              activeColor: const Color(0xFF0037B0),
              onChanged: (value) => setState(() => _selectedMethod = value!),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusCard() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF0037B0), Color(0xFF1D4ED8)],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0037B0).withOpacity(0.2),
            blurRadius: 16,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Status Keanggotaan',
            style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w500),
          ),
          const SizedBox(height: 4),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Aktif & Terverifikasi',
                style: GoogleFonts.plusJakartaSans(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const Icon(Icons.verified, color: Color(0xFF82F5C1), size: 24),
            ],
          ),
          const SizedBox(height: 16),
          const Divider(color: Colors.white10),
          const SizedBox(height: 16),
          const Text(
            'Pembayaran melalui QRIS dan Virtual Account akan terkonfirmasi secara otomatis dalam 5 menit.',
            style: TextStyle(color: Colors.white70, fontSize: 10, height: 1.5),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomPanel() {
    final currencyFormat = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    return Container(
      padding: const EdgeInsets.only(left: 20, right: 20, top: 20, bottom: 40),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.9),
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(24),
          topRight: Radius.circular(24),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 32,
            offset: const Offset(0, -8),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Total Bayar',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      color: const Color(0xFF434655),
                    ),
                  ),
                  Text(
                    currencyFormat.format(widget.amount),
                    style: GoogleFonts.jetBrainsMono(
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                      color: const Color(0xFF0037B0),
                    ),
                  ),
                ],
              ),
              TextButton(
                onPressed: () {},
                child: Row(
                  children: [
                    Text(
                      'Detail Tagihan',
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF0037B0),
                      ),
                    ),
                    const Icon(Icons.chevron_right, size: 16, color: Color(0xFF0037B0)),
                  ],
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
                  '/checkout', 
                  arguments: {'method': _selectedMethod}
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0037B0),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                elevation: 12,
                shadowColor: const Color(0xFF0037B0).withOpacity(0.3),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'Lanjut',
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
    );
  }
}
