import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

class LoanApplicationScreen extends StatefulWidget {
  const LoanApplicationScreen({super.key});

  @override
  State<LoanApplicationScreen> createState() => _LoanApplicationScreenState();
}

class _LoanApplicationScreenState extends State<LoanApplicationScreen> {
  double _loanAmount = 10000000;
  int _tenureMonths = 12;
  String _purpose = 'Modal Kerja';
  final double _monthlyInterestRate = 0.0083; // 0.83%
  
  final TextEditingController _amountController = TextEditingController(text: '10.000.000');
  final NumberFormat _formatter = NumberFormat.decimalPattern('id');
  final NumberFormat _currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void dispose() {
    _amountController.dispose();
    super.dispose();
  }

  void _updateAmount(double value) {
    setState(() {
      _loanAmount = value;
      _amountController.text = _formatter.format(value.toInt());
    });
  }

  double get _monthlyPrincipal => _loanAmount / _tenureMonths;
  double get _monthlyInterest => _loanAmount * _monthlyInterestRate;
  double get _totalMonthlyInstallment => _monthlyPrincipal + _monthlyInterest;
  double get _totalPayment => _totalMonthlyInstallment * _tenureMonths;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9F9FF),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF0037B0)),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Ajukan Pembiayaan',
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.bold,
            fontSize: 18,
            color: const Color(0xFF0037B0),
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildStepIndicator(),
            const SizedBox(height: 24),
            _buildAmountInput(),
            const SizedBox(height: 24),
            _buildTenureGrid(),
            const SizedBox(height: 24),
            _buildAdditionalFields(),
            const SizedBox(height: 32),
            _buildSimulationCard(),
            const SizedBox(height: 100), // Space for bottom button
          ],
        ),
      ),
      bottomSheet: _buildBottomButton(),
    );
  }

  Widget _buildStepIndicator() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'LANGKAH 1 DARI 3',
              style: GoogleFonts.inter(
                fontSize: 10,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF0037B0),
                letterSpacing: 1,
              ),
            ),
            Text(
              'Form Pengajuan',
              style: GoogleFonts.inter(fontSize: 10, color: const Color(0xFF747686)),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(child: Container(height: 6, decoration: BoxDecoration(color: const Color(0xFF0037B0), borderRadius: BorderRadius.circular(10)))),
            const SizedBox(width: 8),
            Expanded(child: Container(height: 6, decoration: BoxDecoration(color: const Color(0xFFD8E3FB), borderRadius: BorderRadius.circular(10)))),
            const SizedBox(width: 8),
            Expanded(child: Container(height: 6, decoration: BoxDecoration(color: const Color(0xFFD8E3FB), borderRadius: BorderRadius.circular(10)))),
          ],
        ),
      ],
    );
  }

  Widget _buildAmountInput() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Jumlah yang Diajukan',
          style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xFF434655)),
        ),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: const Color(0xFFF0F3FF),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            children: [
              Text(
                'Rp',
                style: GoogleFonts.jetBrainsMono(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0037B0),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: TextField(
                  controller: _amountController,
                  keyboardType: TextInputType.number,
                  style: GoogleFonts.jetBrainsMono(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF111C2D),
                  ),
                  decoration: const InputDecoration(
                    border: InputBorder.none,
                    isDense: true,
                  ),
                  onChanged: (value) {
                    final cleanValue = value.replaceAll('.', '');
                    if (cleanValue.isNotEmpty) {
                      _loanAmount = double.tryParse(cleanValue) ?? 0;
                      setState(() {});
                    }
                  },
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [1000000, 2000000, 5000000, 10000000].map((amt) {
              bool isSelected = _loanAmount == amt.toDouble();
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: ChoiceChip(
                  label: Text('${amt ~/ 1000000} Jt'),
                  selected: isSelected,
                  onSelected: (selected) {
                    if (selected) _updateAmount(amt.toDouble());
                  },
                  selectedColor: const Color(0xFF0037B0),
                  labelStyle: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: isSelected ? Colors.white : const Color(0xFF0037B0),
                  ),
                  backgroundColor: const Color(0xFFE7EEFF),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  side: BorderSide.none,
                  showCheckmark: false,
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  Widget _buildTenureGrid() {
    final tenures = [3, 6, 12, 18, 24, 36];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Jangka Waktu',
          style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xFF434655)),
        ),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 3,
            childAspectRatio: 2.5,
            crossAxisSpacing: 8,
            mainAxisSpacing: 8,
          ),
          itemCount: tenures.length,
          itemBuilder: (context, index) {
            bool isSelected = _tenureMonths == tenures[index];
            return InkWell(
              onTap: () => setState(() => _tenureMonths = tenures[index]),
              child: Container(
                decoration: BoxDecoration(
                  color: isSelected ? Colors.white : const Color(0xFFF0F3FF),
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: isSelected ? [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 4)] : null,
                  border: isSelected ? Border.all(color: const Color(0xFF0037B0), width: 1.5) : null,
                ),
                child: Center(
                  child: Text(
                    '${tenures[index]} bln',
                    style: GoogleFonts.inter(
                      fontSize: 13,
                      fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                      color: isSelected ? const Color(0xFF0037B0) : const Color(0xFF434655),
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildAdditionalFields() {
    return Column(
      children: [
        _buildDropdown('Tujuan Pembiayaan', ['Modal Kerja', 'Pendidikan', 'Renovasi Rumah', 'Lainnya']),
      ],
    );
  }

  Widget _buildDropdown(String label, List<String> options) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xFF434655))),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          decoration: BoxDecoration(color: const Color(0xFFF0F3FF), borderRadius: BorderRadius.circular(12)),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: _purpose,
              isExpanded: true,
              icon: const Icon(Icons.expand_more, color: Color(0xFF747686)),
              items: options.map((String value) {
                return DropdownMenuItem<String>(value: value, child: Text(value, style: GoogleFonts.inter(fontSize: 14)));
              }).toList(),
              onChanged: (newValue) => setState(() => _purpose = newValue!),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildSimulationCard() {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: const Color(0xFFFFDDB8), width: 2),
            boxShadow: [BoxShadow(color: const Color(0xFF623C00).withOpacity(0.05), blurRadius: 20, offset: const Offset(0, 10))],
          ),
          child: Column(
            children: [
              const SizedBox(height: 12),
              Text('Angsuran per Bulan', style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w500, color: const Color(0xFF434655))),
              const SizedBox(height: 4),
              Text(
                _currencyFormatter.format(_totalMonthlyInstallment),
                style: GoogleFonts.jetBrainsMono(fontSize: 26, fontWeight: FontWeight.w800, color: const Color(0xFF623C00)),
              ),
              const SizedBox(height: 20),
              Container(
                padding: const EdgeInsets.only(top: 20),
                decoration: const BoxDecoration(border: Border(top: BorderSide(color: Color(0xFFC4C5D7), width: 1, style: BorderStyle.solid))),
                child: Column(
                  children: [
                    _simulationRow('Pokok', _currencyFormatter.format(_monthlyPrincipal)),
                    const SizedBox(height: 8),
                    _simulationRow('Bunga (0.83%)', _currencyFormatter.format(_monthlyInterest)),
                    const Padding(padding: EdgeInsets.symmetric(vertical: 12), child: Divider(color: Color(0xFFD8E3FB))),
                    _simulationRow('Total dibayar', _currencyFormatter.format(_totalPayment), isBold: true),
                  ],
                ),
              ),
            ],
          ),
        ),
        Positioned(
          top: -14,
          left: 0,
          right: 0,
          child: Center(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(color: const Color(0xFF623C00), borderRadius: BorderRadius.circular(20)),
              child: Text(
                'SIMULASI ANGSURAN',
                style: GoogleFonts.inter(fontSize: 10, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: 1),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _simulationRow(String label, String value, {bool isBold = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: GoogleFonts.inter(fontSize: 12, color: isBold ? Colors.black87 : const Color(0xFF434655), fontWeight: isBold ? FontWeight.bold : FontWeight.w500)),
        Text(value, style: GoogleFonts.jetBrainsMono(fontSize: 12, color: isBold ? const Color(0xFF0037B0) : Colors.black87, fontWeight: isBold ? FontWeight.w800 : FontWeight.w500)),
      ],
    );
  }

  Widget _buildBottomButton() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 40),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.8),
        border: const Border(top: BorderSide(color: Color(0xFFF0F3FF), width: 1)),
      ),
      child: ElevatedButton(
        onPressed: () {
          // In a real app, this would be a POST request to submit the loan.
          // For now, we simulate success and navigate to the success screen.
          Navigator.pushNamed(
            context,
            '/loan-success',
            arguments: {
              'amount': _loanAmount,
              'tenure': _tenureMonths,
            },
          );
        },
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF0037B0),
          minimumSize: const Size(double.infinity, 56),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          elevation: 4,
          shadowColor: const Color(0xFF0037B0).withOpacity(0.3),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('Lanjut', style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
            const SizedBox(width: 8),
            const Icon(Icons.arrow_forward_rounded, color: Colors.white, size: 20),
          ],
        ),
      ),
    );
  }
}
