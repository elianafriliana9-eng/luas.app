import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class CheckoutScreen extends StatefulWidget {
  final String method;

  const CheckoutScreen({super.key, required this.method});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _checkoutData;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    _fetchCheckoutData();
  }

  Future<void> _fetchCheckoutData() async {
    final result = await ApiService.postCheckout(widget.method);
    if (result['success']) {
      setState(() {
        _checkoutData = result['data'];
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = result['message'];
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
        appBar: AppBar(title: const Text('Checkout')),
        body: Center(child: Text(_errorMessage)),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF9F9FF),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF0037B0)),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          _getTitle(),
          style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, fontSize: 18),
        ),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            if (widget.method == 'qris') _buildQrisLayout(),
            if (widget.method == 'bri_va') _buildVaLayout(),
            if (widget.method == 'transfer') _buildTransferLayout(),
            
            const SizedBox(height: 24),
            _buildInstructions(),
            const SizedBox(height: 40),
          ],
        ),
      ),
      bottomNavigationBar: _buildBottomPanel(),
    );
  }

  String _getTitle() {
    switch (widget.method) {
      case 'qris': return 'Bayar dengan QRIS';
      case 'bri_va': return 'Virtual Account BRI';
      case 'transfer': return 'Transfer Rekening';
      default: return 'Checkout';
    }
  }

  Widget _buildQrisLayout() {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.all(20),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 20)],
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.network(
                'https://upload.wikimedia.org/wikipedia/commons/a/a2/QRIS_logo.svg', 
                height: 32,
                errorBuilder: (context, error, stackTrace) => Text(
                  'QRIS', 
                  style: GoogleFonts.plusJakartaSans(
                    fontWeight: FontWeight.w900, 
                    fontSize: 24, 
                    color: const Color(0xFF0037B0)
                  )
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          Image.network(_checkoutData!['qris_image'], width: 220, height: 220),
          const SizedBox(height: 24),
          Text(
             'Scan kode QR di atas untuk membayar',
            textAlign: TextAlign.center,
            style: GoogleFonts.inter(fontSize: 14, color: const Color(0xFF434655)),
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFFF0F3FF),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.timer_outlined, size: 16, color: Color(0xFF0037B0)),
                const SizedBox(width: 8),
                Text(
                  'BERAKHIR DALAM 14:59',
                  style: GoogleFonts.jetBrainsMono(fontWeight: FontWeight.bold, fontSize: 12, color: const Color(0xFF0037B0)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildVaLayout() {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.all(20),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
           Row(
            children: [
              Image.network(
                'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/BRI_Logo.svg/1200px-BRI_Logo.svg.png', 
                height: 32,
                errorBuilder: (context, error, stackTrace) => const Icon(Icons.account_balance, color: Color(0xFF0037B0)),
              ),
              const SizedBox(width: 12),
              Text('Virtual Account BRI', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 24),
          Text('NOMOR VIRTUAL ACCOUNT', style: GoogleFonts.inter(fontSize: 12, color: const Color(0xFF747686))),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                _checkoutData!['va_number'],
                style: GoogleFonts.jetBrainsMono(fontSize: 20, fontWeight: FontWeight.bold, color: const Color(0xFF111C2D)),
              ),
              TextButton(
                onPressed: () {
                  Clipboard.setData(ClipboardData(text: _checkoutData!['va_number']));
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Salin berhasil')));
                },
                child: const Text('SALIN', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0037B0))),
              ),
            ],
          ),
          const Divider(),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('TOTAL PEMBAYARAN', style: GoogleFonts.inter(fontSize: 12, color: const Color(0xFF747686))),
              Text(currencyFormat.format(double.tryParse(_checkoutData!['nominal'].toString()) ?? 0), style: GoogleFonts.jetBrainsMono(fontWeight: FontWeight.bold)),
            ],
          ),
        ],
      ),
    );
  }

  int _selectedAccountIndex = 0;

  Widget _buildTransferLayout() {
    final List sourceAccounts = _checkoutData!['source_accounts'];
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
          child: Text('PILIH REKENING SUMBER', style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: const Color(0xFF747686))),
        ),
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: sourceAccounts.length,
          itemBuilder: (context, index) {
            final acc = sourceAccounts[index];
            bool hasBalance = (double.tryParse(acc['saldo'].toString()) ?? 0) >= (double.tryParse(_checkoutData!['nominal'].toString()) ?? 0);
            bool isSelected = _selectedAccountIndex == index && hasBalance;

            return GestureDetector(
              onTap: hasBalance ? () => setState(() => _selectedAccountIndex = index) : null,
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isSelected ? const Color(0xFF0037B0) : (hasBalance ? Colors.transparent : Colors.red.withValues(alpha: 0.3)),
                    width: isSelected ? 2 : 1,
                  ),
                  boxShadow: isSelected ? [BoxShadow(color: const Color(0xFF0037B0).withValues(alpha: 0.1), blurRadius: 10)] : null,
                ),
                child: Row(
                  children: [
                    Icon(
                      isSelected ? Icons.check_circle : Icons.account_balance_wallet_outlined, 
                      color: isSelected ? const Color(0xFF0037B0) : const Color(0xFF747686)
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(acc['produk'] ?? 'Rekening Simpanan', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                          Text(acc['no_rekening'] ?? '-', style: GoogleFonts.jetBrainsMono(fontSize: 12, color: const Color(0xFF747686))),
                        ],
                      ),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          currencyFormat.format(double.tryParse(acc['saldo'].toString()) ?? 0), 
                          style: GoogleFonts.jetBrainsMono(fontSize: 14, fontWeight: FontWeight.bold)
                        ),
                        if (!hasBalance) 
                          const Text('Saldo Tidak Cukup', style: TextStyle(color: Colors.red, fontSize: 10, fontWeight: FontWeight.bold)),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildInstructions() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Cara Pembayaran', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          _stepItem(1, 'Buka aplikasi e-wallet atau mobile banking Anda'),
          _stepItem(2, widget.method == 'qris' ? 'Scan kode QR yang tertera' : 'Pilih menu Transfer / Virtual Account'),
          _stepItem(3, 'Konfirmasi nominal pembayaran yang muncul'),
          _stepItem(4, 'Selesaikan transaksi Anda'),
        ],
      ),
    );
  }

  Widget _stepItem(int no, String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 20,
            height: 20,
            decoration: const BoxDecoration(color: Color(0xFFDCE1FF), shape: BoxShape.circle),
            child: Center(child: Text(no.toString(), style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF0037B0)))),
          ),
          const SizedBox(width: 12),
          Expanded(child: Text(text, style: GoogleFonts.inter(fontSize: 13, color: const Color(0xFF434655)))),
        ],
      ),
    );
  }

  bool _isProcessing = false;

  Future<void> _processPayment() async {
    setState(() => _isProcessing = true);
    
    String? selectedRekeningId;
    if (widget.method == 'transfer') {
      final List sourceAccounts = _checkoutData!['source_accounts'];
      if (sourceAccounts.isNotEmpty && _selectedAccountIndex < sourceAccounts.length) {
        selectedRekeningId = sourceAccounts[_selectedAccountIndex]['id'].toString();
      }
    }

    final result = await ApiService.postPaymentConfirmation(
      widget.method, 
      _checkoutData!['no_transaksi'],
      rekeningId: selectedRekeningId,
    );

    setState(() => _isProcessing = false);

    if (result['success']) {
      _showSuccessDialog();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'] ?? 'Pembayaran gagal')),
      );
    }
  }

  void _showSuccessDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Icon(Icons.check_circle, color: Color(0xFF006C4A), size: 64),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'Pembayaran Berhasil!',
              style: GoogleFonts.plusJakartaSans(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              'Tagihan Anda telah berhasil dibayar. Terima kasih.',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(fontSize: 14, color: const Color(0xFF434655)),
            ),
          ],
        ),
        actions: [
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                Navigator.of(context).pop(); // Close dialog
                Navigator.pushNamedAndRemoveUntil(context, '/dashboard', (route) => false);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0037B0),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Ke Beranda', style: TextStyle(color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomPanel() {
    bool isTransfer = widget.method == 'transfer';
    
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 40),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, -5))],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          SizedBox(
            width: double.infinity,
            height: 56,
            child: ElevatedButton(
              onPressed: _isProcessing 
                ? null 
                : () {
                    if (isTransfer) {
                      _processPayment();
                    } else {
                      Navigator.pushNamedAndRemoveUntil(context, '/dashboard', (route) => false);
                    }
                  },
              style: ElevatedButton.styleFrom(
                backgroundColor: isTransfer ? const Color(0xFF006C4A) : const Color(0xFF0037B0),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              child: _isProcessing
                ? const CircularProgressIndicator(color: Colors.white)
                : Text(
                    isTransfer ? 'Bayar Sekarang' : 'Selesai',
                    style: GoogleFonts.plusJakartaSans(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                  ),
            ),
          ),
        ],
      ),
    );
  }
}
