import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'payment_screen.dart';
import 'profile_screen.dart';
import '../services/api_service.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _dashboardData;
  String _userName = '';
  bool _isLoading = true;
  int _selectedIndex = 0;

  // Colors from Design
  static const Color primaryBlue = Color(0xFF0037B0);
  static const Color primaryContainer = Color(0xFF1D4ED8);
  static const Color secondaryGreen = Color(0xFF006C4A);
  static const Color tertiaryOrange = Color(0xFF825100);
  static const Color surfaceColor = Color(0xFFF9F9FF);
  static const Color onSurfaceVariant = Color(0xFF434655);
  static const Color outlineColor = Color(0xFF747686);

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _userName = prefs.getString('user_name') ?? 'Anggota';
    });
    _fetchDashboard();
  }

  Future<void> _fetchDashboard() async {
    setState(() => _isLoading = true);
    final result = await ApiService.getDashboard();
    if (result['success']) {
      setState(() {
        _dashboardData = result['data'];
        _isLoading = false;
      });
    } else {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Gagal mengambil data')),
        );
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
    ).format(number);
  }

  @override
  Widget build(BuildContext context) {
    Widget body;
    if (_selectedIndex == 2) {
      body = const PaymentScreen(isTab: true);
    } else if (_selectedIndex == 3) {
      body = const ProfileScreen();
    } else {
      body = _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: primaryContainer),
            )
          : RefreshIndicator(
              onRefresh: _fetchDashboard,
              child: SingleChildScrollView(
                child: Column(children: [_buildHeader(), _buildMainContent()]),
              ),
            );
    }

    return PopScope(
      canPop: _selectedIndex == 0,
      onPopInvoked: (didPop) {
        if (didPop) return;
        setState(() {
          _selectedIndex = 0;
        });
      },
      child: Scaffold(
        backgroundColor: surfaceColor,
        body: body,
        floatingActionButton: Container(
          width: 64,
          height: 64,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [primaryContainer, primaryBlue],
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: primaryBlue.withValues(alpha: 0.3),
                blurRadius: 12,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: () {
                Navigator.pushNamed(context, '/loan-application');
              },
              borderRadius: BorderRadius.circular(20),
              child: const Icon(
                Icons.add_rounded,
                color: Colors.white,
                size: 32,
              ),
            ),
          ),
        ),
        floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
        bottomNavigationBar: _buildBottomNav(),
      ),
    );
  }

  Widget _buildHeader() {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        Container(
          height: 280,
          width: double.infinity,
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [primaryContainer, primaryBlue],
            ),
            borderRadius: BorderRadius.only(
              bottomLeft: Radius.circular(32),
              bottomRight: Radius.circular(32),
            ),
          ),
          padding: const EdgeInsets.fromLTRB(24, 60, 24, 0),
          child: Column(
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
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.2),
                            width: 2,
                          ),
                          image: const DecorationImage(
                            image: NetworkImage(
                              'https://i.pravatar.cc/150?u=siti',
                            ),
                            fit: BoxFit.cover,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Selamat pagi,',
                            style: GoogleFonts.inter(
                              color: Colors.white.withValues(alpha: 0.7),
                              fontSize: 12,
                            ),
                          ),
                          Text(
                            _userName.split(' ')[0],
                            style: GoogleFonts.plusJakartaSans(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.notifications_outlined,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              // Mini Stats Row
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'PEMBIAYAAN AKTIF',
                          style: GoogleFonts.inter(
                            color: Colors.white.withValues(alpha: 0.7),
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _formatCurrency(_dashboardData?['total_tagihan']),
                          style: GoogleFonts.jetBrainsMono(
                            color: Colors.white,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          'ANGSURAN BERIKUTNYA',
                          style: GoogleFonts.inter(
                            color: Colors.white.withValues(alpha: 0.7),
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            Text(
                              _dashboardData?['next_installment']?['tanggal'] ??
                                  '-',
                              style: GoogleFonts.jetBrainsMono(
                                color: Colors.white,
                                fontSize: 14,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(width: 8),
                            if (_dashboardData?['next_installment'] != null)
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFFDDB8),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  _dashboardData?['next_installment']?['status'] ??
                                      '',
                                  style: GoogleFonts.inter(
                                    color: const Color(0xFF2A1700),
                                    fontSize: 9,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        Positioned(
          bottom: -40,
          left: 24,
          right: 24,
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.06),
                  blurRadius: 32,
                  offset: const Offset(0, 12),
                ),
              ],
            ),
            child: Stack(
              children: [
                Positioned(
                  left: 0,
                  top: 0,
                  bottom: 0,
                  child: Container(
                    width: 4,
                    decoration: const BoxDecoration(
                      color: primaryBlue,
                      borderRadius: BorderRadius.only(
                        topLeft: Radius.circular(4),
                        bottomLeft: Radius.circular(4),
                      ),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.only(left: 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'TOTAL SALDO SIMPANAN',
                            style: GoogleFonts.inter(
                              color: onSurfaceVariant,
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                              letterSpacing: 1,
                            ),
                          ),
                          const Icon(
                            Icons.visibility_outlined,
                            size: 18,
                            color: outlineColor,
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.baseline,
                        textBaseline: TextBaseline.alphabetic,
                        children: [
                          Text(
                            'Rp',
                            style: GoogleFonts.plusJakartaSans(
                              color: primaryBlue,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            NumberFormat('#,###', 'id_ID').format(
                              double.tryParse(
                                    (_dashboardData?['total_simpanan'] ?? 0)
                                        .toString(),
                                  ) ??
                                  0,
                            ),
                            style: GoogleFonts.plusJakartaSans(
                              color: primaryBlue,
                              fontSize: 28,
                              fontWeight: FontWeight.w800,
                              letterSpacing: -1,
                            ),
                          ),
                        ],
                      ),
                      Text(
                        'Saldo per ${DateFormat('d MMMM yyyy', 'id_ID').format(DateTime.now())}',
                        style: GoogleFonts.jetBrainsMono(
                          color: outlineColor,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildMainContent() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 64, 24, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildPayrollInfo(),
          const SizedBox(height: 24),
          _buildQuickActions(),
          const SizedBox(height: 32),
          _buildRekeningSection(),
          const SizedBox(height: 32),
          _buildTransactionSection(),
        ],
      ),
    );
  }

  Widget _buildPayrollInfo() {
    final payrollInfo = _dashboardData?['payroll_info'];
    if (payrollInfo == null) return const SizedBox.shrink();

    final gajiPokok =
        double.tryParse(payrollInfo['gaji_pokok'].toString()) ?? 0;
    final totalPotongan =
        double.tryParse(payrollInfo['total_potongan'].toString()) ?? 0;
    final gajiDiterima =
        double.tryParse(payrollInfo['gaji_diterima'].toString()) ?? 0;
    final tanggalGajian = payrollInfo['tanggal_gajian'] ?? 25;
    final autoPotongAktif = payrollInfo['auto_potong_aktif'] ?? false;
    final departemen = payrollInfo['departemen'] ?? '-';
    final jabatan = payrollInfo['jabatan'] ?? '-';

    final now = DateTime.now();
    DateTime nextPayday = DateTime(now.year, now.month, tanggalGajian);
    if (now.day >= tanggalGajian) {
      // Next month
      final nextMonth = now.month + 1;
      if (nextMonth > 12) {
        nextPayday = DateTime(now.year + 1, 1, tanggalGajian);
      } else {
        nextPayday = DateTime(now.year, nextMonth, tanggalGajian);
      }
    }
    final daysUntilPayday = nextPayday.difference(now).inDays;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF006C4A), width: 1),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 20,
            offset: const Offset(0, 8),
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
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF006C4A).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.badge_outlined,
                      color: Color(0xFF006C4A),
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'INFO KARYAWAN',
                        style: GoogleFonts.inter(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF434655),
                          letterSpacing: 1,
                        ),
                      ),
                      Text(
                        '$departemen — $jabatan',
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF111C2D),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              if (autoPotongAktif)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF006C4A),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.autorenew,
                        color: Colors.white,
                        size: 12,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'AUTO POTONG',
                        style: GoogleFonts.inter(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          ),
          const SizedBox(height: 16),
          // Salary breakdown
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFF0F3FF),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                _salaryRow('Gaji Pokok', _formatCurrency(gajiPokok)),
                const SizedBox(height: 8),
                if (totalPotongan > 0) ...[
                  _salaryRow(
                    'Potongan Angsuran',
                    '-${_formatCurrency(totalPotongan)}',
                    valueColor: const Color(0xFFBA1A1A),
                  ),
                  const SizedBox(height: 8),
                ],
                const Divider(height: 1, color: Color(0xFFDCE1FF)),
                const SizedBox(height: 8),
                _salaryRow(
                  'Gaji Diterima',
                  _formatCurrency(gajiDiterima),
                  isBold: true,
                  valueColor: const Color(0xFF006C4A),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Gajian berikutnya: ${DateFormat('d MMMM yyyy', 'id_ID').format(nextPayday)}',
                style: GoogleFonts.inter(
                  fontSize: 11,
                  color: const Color(0xFF434655),
                  fontWeight: FontWeight.w500,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFDDB8),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '$daysUntilPayday hari lagi',
                  style: GoogleFonts.inter(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF623C00),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _salaryRow(
    String label,
    String value, {
    bool isBold = false,
    Color valueColor = const Color(0xFF111C2D),
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 12,
            fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
            color: const Color(0xFF434655),
          ),
        ),
        Text(
          value,
          style: GoogleFonts.jetBrainsMono(
            fontSize: 13,
            fontWeight: isBold ? FontWeight.w800 : FontWeight.w600,
            color: valueColor,
          ),
        ),
      ],
    );
  }

  Widget _buildQuickActions() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          _buildActionItem(Icons.add_card, 'Setor', () {}),
          _buildActionItem(Icons.account_balance_wallet, 'Tarik', () {}),
          _buildActionItem(Icons.payments_outlined, 'Bayar', () {
            Navigator.pushNamed(context, '/payment');
          }),
          _buildActionItem(Icons.send_rounded, 'Transfer', () {}),
        ],
      ),
    );
  }

  Widget _buildActionItem(IconData icon, String label, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: const Color(0xFFDCE1FF),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: primaryBlue),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: onSurfaceVariant,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRekeningSection() {
    final rekenings = _dashboardData?['rekening'] as List? ?? [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Rekening Saya',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF111C2D),
          ),
        ),
        const SizedBox(height: 16),
        SizedBox(
          height: 130,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: rekenings.length,
            clipBehavior: Clip.none,
            itemBuilder: (context, index) {
              final rek = rekenings[index];
              if (rek == null) return const SizedBox();
              final produkName = (rek['produk'] ?? 'Simpanan').toString();
              final isSukarela = produkName.toLowerCase().contains('sukarela');
              final isWajib = produkName.toLowerCase().contains('wajib');

              Color bgColor = tertiaryOrange;
              if (isSukarela) bgColor = primaryBlue;
              if (isWajib) bgColor = secondaryGreen;

              return Container(
                width: 200,
                margin: const EdgeInsets.only(right: 16),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: bgColor.withValues(alpha: 0.3),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Stack(
                  children: [
                    Positioned(
                      top: -20,
                      right: -20,
                      child: Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.1),
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          produkName.toUpperCase(),
                          style: GoogleFonts.inter(
                            color: Colors.white.withValues(alpha: 0.8),
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            letterSpacing: 1,
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Saldo Tersedia',
                              style: GoogleFonts.jetBrainsMono(
                                color: Colors.white.withValues(alpha: 0.8),
                                fontSize: 10,
                              ),
                            ),
                            Text(
                              _formatCurrency(rek['saldo']),
                              style: GoogleFonts.plusJakartaSans(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildTransactionSection() {
    final transactions = _dashboardData?['recent_transactions'] as List? ?? [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Transaksi Terkini',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF111C2D),
              ),
            ),
            Text(
              'Lihat Semua',
              style: GoogleFonts.inter(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: primaryBlue,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: transactions.length,
          separatorBuilder: (context, index) => const SizedBox(height: 12),
          itemBuilder: (context, index) {
            final trx = transactions[index];
            final isDebit = trx['is_debit'] ?? false;

            return Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF0F3FF),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: isDebit
                          ? const Color(0xFFFFDAD6)
                          : const Color(0xFF82F5C1).withValues(alpha: 0.3),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      isDebit
                          ? Icons.arrow_outward_rounded
                          : Icons.trending_up_rounded,
                      color: isDebit
                          ? const Color(0xFFBA1A1A)
                          : const Color(0xFF006C4A),
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          trx['keterangan'] ??
                              (isDebit ? 'Tarik Tunai' : 'Setor Tunai'),
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF111C2D),
                          ),
                        ),
                        Text(
                          trx['tanggal'],
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            color: outlineColor,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '${isDebit ? '-' : '+'}${_formatCurrency(trx['nominal'])}',
                        style: GoogleFonts.jetBrainsMono(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: isDebit
                              ? const Color(0xFFBA1A1A)
                              : const Color(0xFF006C4A),
                        ),
                      ),
                      const Text(
                        'Sukses',
                        style: TextStyle(fontSize: 10, color: outlineColor),
                      ),
                    ],
                  ),
                ],
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildBottomNav() {
    return BottomAppBar(
      height: 85,
      color: Colors.white,
      shape: const AutomaticNotchedShape(
        RoundedRectangleBorder(
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(24),
            topRight: Radius.circular(24),
          ),
        ),
        RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(20)),
        ),
      ),
      notchMargin: 8,
      padding: EdgeInsets.zero,
      child: Row(
        children: [
          Expanded(child: _buildNavitem(Icons.home_rounded, 'Beranda', 0)),
          Expanded(
            child: _buildNavitem(
              Icons.account_balance_wallet_outlined,
              'Dompet Saya',
              1,
            ),
          ),
          const SizedBox(width: 70), // Center space for FAB
          Expanded(
            child: _buildNavitem(Icons.payments_outlined, 'Pembayaran', 2),
          ),
          Expanded(
            child: _buildNavitem(Icons.person_outline_rounded, 'Profil', 3),
          ),
        ],
      ),
    );
  }

  Widget _buildNavitem(IconData icon, String label, int index) {
    bool isSelected = _selectedIndex == index;
    return GestureDetector(
      onTap: () => setState(() => _selectedIndex = index),
      behavior: HitTestBehavior.opaque,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: isSelected ? primaryBlue : outlineColor, size: 24),
          const SizedBox(height: 4),
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 10,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              color: isSelected ? primaryBlue : outlineColor,
            ),
          ),
        ],
      ),
    );
  }
}
