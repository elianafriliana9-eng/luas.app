import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import '../widgets/custom_bottom_nav.dart';
import 'simpanan_pokok_screen.dart';
import 'simpanan_sukarela_screen.dart';
import 'simpanan_wajib_screen.dart';
import 'data_anggota_screen.dart';
import 'history_transaksi_screen.dart';
import 'pinjaman_anggota_screen.dart';
import 'semua_simpanan_screen.dart';
import 'notification_screen.dart';
import 'setting_screen.dart';
import 'bayar_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  Map<String, dynamic>? _dashboardData;
  String _userName = '';
  bool _isLoading = true;
  bool _hideSaldo = false;

  // Web Palette
  static const Color primary = Color(0xFF0D47A1); // Dark blue from Opsi 3
  static const Color surface = Color(0xFFF8FAFC);
  static const Color textPrimary = Color(0xFF1E293B);
  static const Color textSecondary = Color(0xFF64748B);

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _userName = prefs.getString('user_name') ?? 'Anggota';
      // Format name to uppercase first name as in mockup "BUDI"
      if (_userName.isNotEmpty) {
        _userName = _userName.split(' ')[0].toUpperCase();
      }
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
    if (_hideSaldo) return 'Rp •••••••';
    if (value == null) return 'Rp 0';
    final number = double.tryParse(value.toString()) ?? 0;
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    ).format(number).trim();
  }

  int _getSaldoByProduk(String keyword) {
    if (_dashboardData == null || _dashboardData!['rekening'] == null) return 0;
    final rekenings = _dashboardData!['rekening'] as List;
    double total = 0;
    for (var rek in rekenings) {
      if ((rek['produk'] as String).toLowerCase().contains(keyword.toLowerCase())) {
        total += double.tryParse(rek['saldo'].toString()) ?? 0;
      }
    }
    return total.toInt();
  }

  int _selectedIndex = 0;

  void _onItemTapped(int index) {
    if (index == 2) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const BayarScreen()),
      );
      return;
    }
    if (index == 3) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const SettingScreen()),
      );
      return;
    }
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: Colors.white,
        body: Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(primary),
          ),
        ),
      );
    }

    // Get Saldo Total from data, or fallback
    final totalSaldo = _dashboardData?['total_saldo'] ?? 0;

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: Colors.white, // Opsi 3 uses full white for the content background
      drawer: _buildDrawer(),
      bottomNavigationBar: CustomBottomNav(
        selectedIndex: _selectedIndex,
        onItemTapped: _onItemTapped,
      ),
      body: Stack(
        children: [
          // Blue Background (Back)
          Container(
            height: MediaQuery.of(context).size.height * 0.5,
            color: primary,
          ),
          // Scrollable Content (Front)
          SafeArea(
            bottom: false,
            child: SingleChildScrollView(
              child: Column(
                children: [
                  // Header Section (Transparent)
                  Padding(
                    padding: const EdgeInsets.only(top: 16, left: 24, right: 24, bottom: 32),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // App Bar
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            GestureDetector(
                              onTap: () {
                                _scaffoldKey.currentState?.openDrawer();
                              },
                              child: const Icon(Icons.menu, color: Colors.white, size: 28),
                            ),
                            GestureDetector(
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => const NotificationScreen(),
                                  ),
                                );
                              },
                              child: Stack(
                                children: [
                                  const Icon(Icons.notifications_none_rounded, color: Colors.white, size: 28),
                                  Positioned(
                                    right: 2,
                                    top: 2,
                                    child: Container(
                                      width: 10,
                                      height: 10,
                                      decoration: const BoxDecoration(
                                        color: Color(0xFFEF4444),
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                  )
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 32),
                        // Welcome Text
                        Text(
                          'Selamat datang,',
                          style: GoogleFonts.poppins(
                            color: Colors.white70,
                            fontSize: 14,
                          ),
                        ),
                        Text(
                          _userName,
                          style: GoogleFonts.poppins(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 24),
                        // Dompet Koperasi
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Dompet Koperasi',
                                  style: GoogleFonts.poppins(
                                    color: Colors.white70,
                                    fontSize: 12,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    Text(
                                      _formatCurrency(totalSaldo),
                                      style: GoogleFonts.poppins(
                                        color: Colors.white,
                                        fontSize: 28,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    GestureDetector(
                                      onTap: () {
                                        setState(() {
                                          _hideSaldo = !_hideSaldo;
                                        });
                                      },
                                      child: Icon(
                                        _hideSaldo ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                        color: Colors.white70,
                                        size: 20,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),
                        // Action Buttons (Bayar & Top Up)
                        Row(
                          children: [
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: () {
                                  Navigator.push(context, MaterialPageRoute(builder: (_) => const BayarScreen()));
                                },
                                icon: const Icon(Icons.qr_code_scanner_rounded, size: 20, color: primary),
                                label: Text(
                                  'Bayar',
                                  style: GoogleFonts.poppins(
                                    fontWeight: FontWeight.w600,
                                    color: primary,
                                  ),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.white,
                                  foregroundColor: primary,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: () {
                                  // TODO: Navigate to Top Up Screen
                                },
                                icon: const Icon(Icons.add_card_rounded, size: 20, color: Colors.white),
                                label: Text(
                                  'Top Up',
                                  style: GoogleFonts.poppins(
                                    fontWeight: FontWeight.w600,
                                    color: Colors.white,
                                  ),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.white.withValues(alpha: 0.2),
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  
                  // White Body Content (Overlapping the blue background)
                  Container(
                    width: double.infinity,
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.only(
                        topLeft: Radius.circular(30),
                        topRight: Radius.circular(30),
                      ),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(24, 32, 24, 24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Saldo Simpanan Header
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Saldo Simpanan',
                                style: GoogleFonts.poppins(
                                  color: textPrimary,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          // Saldo Simpanan Card
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
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
                              border: Border.all(color: Colors.grey.shade100),
                            ),
                            child: Column(
                              children: [
                                _buildSimpananItem(
                                  icon: Icons.account_balance_wallet_outlined,
                                  iconColor: primary,
                                  title: 'Simpanan Pokok',
                                  amount: _formatCurrency(_getSaldoByProduk('pokok')),
                                  onTap: () {
                                    Navigator.push(context, MaterialPageRoute(builder: (_) => const SimpananPokokScreen()));
                                  },
                                ),
                                Divider(color: Colors.grey.shade100, height: 1),
                                _buildSimpananItem(
                                  icon: Icons.savings_outlined,
                                  iconColor: const Color(0xFF059669), // Green
                                  title: 'Simpanan Wajib',
                                  amount: _formatCurrency(_getSaldoByProduk('wajib')),
                                  onTap: () {
                                    Navigator.push(context, MaterialPageRoute(builder: (_) => const SimpananWajibScreen()));
                                  },
                                ),
                                Divider(color: Colors.grey.shade100, height: 1),
                                _buildSimpananItem(
                                  icon: Icons.volunteer_activism_outlined,
                                  iconColor: const Color(0xFFF59E0B), // Orange
                                  title: 'Simpanan Sukarela',
                                  amount: _formatCurrency(_getSaldoByProduk('sukarela')),
                                  onTap: () {
                                    Navigator.push(context, MaterialPageRoute(builder: (_) => const SimpananSukarelaScreen()));
                                  },
                                ),
                              ],
                            ),
                          ),
                          
                          const SizedBox(height: 32),
                          
                          // Akses Cepat Header
                          Text(
                            'Akses Cepat',
                            style: GoogleFonts.poppins(
                              color: textPrimary,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 16),
                          // Akses Cepat Grid
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              _buildAksesCepatItem(
                                icon: Icons.receipt_long_outlined,
                                title: 'History\nTransaksi',
                                onTap: () {
                                  Navigator.push(context, MaterialPageRoute(builder: (_) => const HistoryTransaksiScreen()));
                                },
                              ),
                              _buildAksesCepatItem(
                                icon: Icons.person_outline_rounded,
                                title: 'Data\nAnggota',
                                onTap: () {
                                  Navigator.push(context, MaterialPageRoute(builder: (_) => const DataAnggotaScreen()));
                                },
                              ),
                              _buildAksesCepatItem(
                                icon: Icons.groups_outlined,
                                title: 'Pinjaman\nAnggota',
                                onTap: () {
                                  Navigator.push(context, MaterialPageRoute(builder: (_) => const PinjamanAnggotaScreen()));
                                },
                              ),
                            ],
                          ),
                          
                          const SizedBox(height: 32),
                          
                          // Butuh Dana Tambahan Banner
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(24),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFF0D47A1), Color(0xFF1E63E9)],
                                begin: Alignment.centerLeft,
                                end: Alignment.centerRight,
                              ),
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: primary.withValues(alpha: 0.2),
                                  blurRadius: 10,
                                  offset: const Offset(0, 8),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Butuh Dana Tambahan?',
                                        style: GoogleFonts.poppins(
                                          color: Colors.white,
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      Text(
                                        'Ajukan pinjaman sekarang',
                                        style: GoogleFonts.poppins(
                                          color: Colors.white70,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withOpacity(0.2),
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(
                                    Icons.account_balance_wallet_outlined,
                                    color: Colors.white,
                                    size: 28,
                                  ),
                                )
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDrawer() {
    return Drawer(
      child: Container(
        color: surface,
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.only(top: 60, bottom: 24, left: 24, right: 24),
              decoration: const BoxDecoration(
                color: primary,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                    ),
                    child: const Center(
                      child: Icon(Icons.person, size: 36, color: primary),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    _userName,
                    style: GoogleFonts.poppins(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Anggota Aktif',
                    style: GoogleFonts.poppins(
                      color: Colors.white70,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 8),
                children: [
                  _buildDrawerItem(
                    icon: Icons.info_outline_rounded,
                    title: 'Tentang Koperasi',
                    onTap: () {
                      Navigator.pop(context);
                    },
                  ),
                  _buildDrawerItem(
                    icon: Icons.help_outline_rounded,
                    title: 'Pusat Bantuan / FAQ',
                    onTap: () {
                      Navigator.pop(context);
                    },
                  ),
                  _buildDrawerItem(
                    icon: Icons.description_outlined,
                    title: 'Syarat & Ketentuan',
                    onTap: () {
                      Navigator.pop(context);
                    },
                  ),
                  _buildDrawerItem(
                    icon: Icons.support_agent_rounded,
                    title: 'Hubungi Kami',
                    onTap: () {
                      Navigator.pop(context);
                    },
                  ),
                ],
              ),
            ),
            const Divider(height: 1),
            _buildDrawerItem(
              icon: Icons.logout_rounded,
              title: 'Keluar',
              textColor: const Color(0xFFEF4444),
              iconColor: const Color(0xFFEF4444),
              onTap: () async {
                Navigator.pop(context);
                final prefs = await SharedPreferences.getInstance();
                await prefs.clear();
                if (mounted) {
                  Navigator.pushReplacementNamed(context, '/login');
                }
              },
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawerItem({
    required IconData icon,
    required String title,
    required VoidCallback onTap,
    Color? textColor,
    Color? iconColor,
  }) {
    return ListTile(
      leading: Icon(icon, color: iconColor ?? textSecondary, size: 24),
      title: Text(
        title,
        style: GoogleFonts.poppins(
          color: textColor ?? textPrimary,
          fontSize: 14,
          fontWeight: FontWeight.w500,
        ),
      ),
      onTap: onTap,
    );
  }

  Widget _buildSimpananItem({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String amount,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: iconColor.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: iconColor, size: 24),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Text(
                title,
                style: GoogleFonts.poppins(
                  color: textPrimary,
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            Text(
              amount,
              style: GoogleFonts.poppins(
                color: textPrimary,
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAksesCepatItem({
    required IconData icon,
    required String title,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        width: MediaQuery.of(context).size.width * 0.27,
        padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 8),
        decoration: BoxDecoration(
          color: primary,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: primary.withValues(alpha: 0.2),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: Colors.white, size: 32),
            const SizedBox(height: 12),
            Text(
              title,
              textAlign: TextAlign.center,
              style: GoogleFonts.poppins(
                color: Colors.white,
                fontSize: 11,
                fontWeight: FontWeight.w500,
                height: 1.2,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
