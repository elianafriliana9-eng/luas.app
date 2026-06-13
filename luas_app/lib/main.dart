import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/payment_screen.dart';
import 'screens/payment_method_screen.dart';
import 'screens/checkout_screen.dart';
import 'screens/loan_application_screen.dart';
import 'screens/loan_success_screen.dart';
import 'screens/pay_later_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize date formatting for Indonesia
  await initializeDateFormatting('id_ID', null);

  // Check for existing token
  // For now, always start from login as requested
  // final token = await ApiService.getToken();

  runApp(const LuasApp(initialRoute: '/login'));
}

class LuasApp extends StatelessWidget {
  final String initialRoute;

  const LuasApp({super.key, required this.initialRoute});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'LUAS Apps',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0D47A1),
          primary: const Color(0xFF0D47A1),
          secondary: const Color(0xFF0D47A1), // We can use the dark blue as main accents
        ),
        textTheme: GoogleFonts.poppinsTextTheme(Theme.of(context).textTheme),
      ),
      initialRoute: initialRoute,
      onGenerateRoute: (settings) {
        if (settings.name == '/payment-method') {
          final args = settings.arguments as Map<String, dynamic>;
          return MaterialPageRoute(
            builder: (context) => PaymentMethodScreen(amount: args['amount']),
          );
        }
        if (settings.name == '/checkout') {
          final args = settings.arguments as Map<String, dynamic>;
          return MaterialPageRoute(
            builder: (context) => CheckoutScreen(method: args['method']),
          );
        }
        if (settings.name == '/loan-success') {
          final args = settings.arguments as Map<String, dynamic>;
          return MaterialPageRoute(
            builder: (context) => LoanSuccessScreen(
              amount: args['amount'],
              tenure: args['tenure'],
            ),
          );
        }
        return null;
      },
      routes: {
        '/login': (context) => const LoginScreen(),
        '/dashboard': (context) => const DashboardScreen(),
        '/payment': (context) => const PaymentScreen(),
        '/loan-application': (context) => const LoanApplicationScreen(),
        '/pay-later': (context) => const PayLaterScreen(),
      },
    );
  }
}
