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

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen>
    with SingleTickerProviderStateMixin {
  Map<String, dynamic>? _dashboardData;
  String _userName = '';
  bool _isLoading = true;

  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  // Web Palette
  static const Color primary = Color(0xFF1D4ED8);
  static const Color primaryLight = Color(0xFF3B82F6);
  static const Color secondary = Color(0xFF059669);
  static const Color tertiary = Color(0xFFF59E0B);
  static const Color danger = Color(0xFFDC2626);
  static const Color neutralDark = Color(0xFF0F172A);
  static const Color neutral = Color(0xFF1E293B);
  static const Color surface = Color(0xFFF8FAFC);

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _animationController,
        curve: const Interval(0.0, 1.0, curve: Curves.easeOut),
      ),
    );
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0.0, 0.05),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _animationController,
        curve: const Interval(0.0, 1.0, curve: Curves.easeOutCubic),
      ),
    );

    _loadInitialData();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
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
      _animationController.forward();
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
    if (value == null) return '0';
    final number = double.tryParse(value.toString()) ?? 0;
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: '',
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

    return Scaffold(
      backgroundColor: Colors.white,
      bottomNavigationBar: CustomBottomNav(
        selectedIndex: _selectedIndex,
        onItemTapped: _onItemTapped,
      ),
      body: Stack(
        children: [
          // Dynamic Header Background (Primary Web Palette)
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            height: MediaQuery.of(context).size.height * 0.45,
            child: Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [primary, primaryLight],
                ),
              ),
            ),
          ),
          SafeArea(
            bottom: false,
            child: Column(
              children: [
                _buildHeader(),
                Expanded(
                  child: SlideTransition(
                    position: _slideAnimation,
                    child: FadeTransition(
                      opacity: _fadeAnimation,
                      child: _buildMainContent(),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
      child: Stack(
        alignment: Alignment.topCenter,
        clipBehavior: Clip.none,
        children: [
          Align(
            alignment: Alignment.topRight,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(
                Icons.settings_rounded,
                color: Colors.white,
                size: 24,
              ),
            ),
          ),
          Column(
            children: [
              Container(
                width: 76,
                height: 76,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: primary.withValues(alpha: 0.3),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: Image.network(
                  'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Koperasi_Indonesia_Logo.svg/1200px-Koperasi_Indonesia_Logo.svg.png',
                  errorBuilder: (context, error, stackTrace) =>
                      const Icon(Icons.sync, color: tertiary, size: 40),
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'KOPERASI',
                style: GoogleFonts.plusJakartaSans(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 2,
                ),
              ),
              Text(
                'MAJU BERSAMA',
                style: GoogleFonts.plusJakartaSans(
                  color: Colors.white.withValues(alpha: 0.7),
                  fontSize: 9,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1.5,
                ),
              ),
              const SizedBox(height: 28),
              Text(
                'Mobile KOPERASI',
                style: GoogleFonts.plusJakartaSans(
                  color: Colors.white,
                  fontSize: 30,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                'Dashboard',
                style: GoogleFonts.inter(
                  color: Colors.white.withValues(alpha: 0.9),
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMainContent() {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        color: surface,
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(40),
          topRight: Radius.circular(40),
        ),
      ),
      child: ClipRRect(
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(40),
          topRight: Radius.circular(40),
        ),
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(24, 32, 24, 40),
          child: Column(
            children: [
              _buildProfileCard(),
              const SizedBox(height: 40),
              _buildGridMenu(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProfileCard() {
    final saldo = _dashboardData?['total_simpanan'] ?? 0;
    final firstName = _userName.split(' ')[0].toUpperCase();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(32),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
        border: Border.all(color: const Color(0xFFF1F5F9), width: 1), // slate-100
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Row(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  padding: const EdgeInsets.all(2),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white,
                    border: Border.all(color: primaryLight, width: 2),
                  ),
                  child: Container(
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      image: DecorationImage(
                        image: NetworkImage(
                          'https://ui-avatars.com/api/?name=$firstName&background=1D4ED8&color=ffffff&bold=true&size=150',
                        ),
                        fit: BoxFit.cover,
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
                        'Hi, $firstName',
                        style: GoogleFonts.plusJakartaSans(
                          color: neutralDark,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'DOMPET KOPERASI',
                        style: GoogleFonts.inter(
                          color: const Color(0xFF64748B), // slate-500
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Rp. ${_formatCurrency(saldo)}',
                        style: GoogleFonts.jetBrainsMono(
                          color: primary,
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.5,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: const Color(0xFFFEE2E2), // red-100
              shape: BoxShape.circle,
            ),
            child: IconButton(
              icon: const Icon(
                Icons.exit_to_app_rounded,
                color: danger,
                size: 22,
              ),
              onPressed: () {},
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGridMenu() {
    // Menu colors precisely adapted from web Tailwind palette
    final menus = [
      {
        'title': 'Simpanan\nPokok',
        'value': 'Rp ${_formatCurrency(_getSaldoByProduk('Pokok'))}',
        'icon_path': 'assets/icons/pokok.png',
        'iconColor': primary,
        'bgColor': const Color(0xFFEFF6FF), // blue-50
        'onTap': () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => SimpananPokokScreen(dashboardData: _dashboardData),
          ),
        ),
      },
      {
        'title': 'Simpanan\nWajib',
        'value': 'Rp ${_formatCurrency(_getSaldoByProduk('Wajib'))}',
        'icon_path': 'assets/icons/wajib.png',
        'iconColor': secondary,
        'bgColor': const Color(0xFFECFDF5), // emerald-50
        'onTap': () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => SimpananWajibScreen(dashboardData: _dashboardData),
          ),
        ),
      },
      {
        'title': 'Simpanan\nSukarela',
        'value': 'Rp ${_formatCurrency(_getSaldoByProduk('Sukarela'))}',
        'icon_path': 'assets/icons/sukarela.png',
        'iconColor': tertiary,
        'bgColor': const Color(0xFFFFFBEB), // amber-50
        'onTap': () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => SimpananSukarelaScreen(dashboardData: _dashboardData),
          ),
        ),
      },
      {
        'title': 'History\nTransaksi',
        'value': null,
        'icon_path': 'assets/icons/history.png',
        'iconColor': danger,
        'bgColor': const Color(0xFFFEF2F2), // red-50
      },
      {
        'title': 'Data\nAnggota',
        'value': null,
        'icon_path': 'assets/icons/profile.png',
        'iconColor': const Color(0xFF7C3AED), // violet-600
        'bgColor': const Color(0xFFF5F3FF), // violet-50
        'onTap': () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => const DataAnggotaScreen(),
          ),
        ),
      },
    ];

    return GridView.builder(
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      padding: EdgeInsets.zero,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        childAspectRatio: 0.72,
        crossAxisSpacing: 16,
        mainAxisSpacing: 24,
      ),
      itemCount: menus.length,
      itemBuilder: (context, index) {
        final menu = menus[index];
        return TweenAnimationBuilder<double>(
          tween: Tween(begin: 0.0, end: 1.0),
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeOutBack,
          builder: (context, value, child) {
            return Transform.scale(
              scale: value,
              child: child,
            );
          },
          child: AnimatedMenuCard(menu: menu),
        );
      },
    );
  }
}

class AnimatedMenuCard extends StatefulWidget {
  final Map<String, dynamic> menu;

  const AnimatedMenuCard({super.key, required this.menu});

  @override
  State<AnimatedMenuCard> createState() => _AnimatedMenuCardState();
}

class _AnimatedMenuCardState extends State<AnimatedMenuCard> {
  bool _isPressed = false;

  @override
  Widget build(BuildContext context) {
    final bgColor = widget.menu['bgColor'] as Color;
    final iconColor = widget.menu['iconColor'] as Color;

    return GestureDetector(
      onTapDown: (_) => setState(() => _isPressed = true),
      onTapUp: (_) {
        setState(() => _isPressed = false);
      },
      onTapCancel: () => setState(() => _isPressed = false),
      onTap: widget.menu['onTap'] as void Function()?,
      child: AnimatedScale(
        scale: _isPressed ? 0.92 : 1.0,
        duration: const Duration(milliseconds: 100),
        child: Column(
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: iconColor.withValues(alpha: 0.1),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Image.asset(
                widget.menu['icon_path'] as String,
                width: 10,
                height: 10,
                fit: BoxFit.contain,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              widget.menu['title'] as String,
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                color: const Color(0xFF475569), // slate-600
                fontSize: 11,
                fontWeight: FontWeight.w600,
                height: 1.2,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            if (widget.menu['value'] != null) ...[
              const SizedBox(height: 6),
              Text(
                widget.menu['value'] as String,
                textAlign: TextAlign.center,
                style: GoogleFonts.jetBrainsMono(
                  color: iconColor,
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.5,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ],
        ),
      ),
    );
  }
}
