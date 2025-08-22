import React, { useState, useEffect } from 'react';
import './Billing.css';

const InvoiceList = () => {
  const [invoices, setInvoices] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchInvoices();
  }, []);

  const fetchInvoices = async () => {
    setLoading(true);
    setError('');
    
    try {
      // In a real app, this would be an API call
      // For now, we'll simulate with mock data
      const mockInvoices = [
        {
          id: 1,
          appointmentId: 101,
          amount: 150.00,
          status: 'paid',
          issuedDate: '2023-08-01',
          dueDate: '2023-08-15',
          paidDate: '2023-08-05'
        },
        {
          id: 2,
          appointmentId: 102,
          amount: 75.50,
          status: 'pending',
          issuedDate: '2023-08-10',
          dueDate: '2023-08-25',
          paidDate: null
        },
        {
          id: 3,
          appointmentId: 103,
          amount: 200.00,
          status: 'overdue',
          issuedDate: '2023-07-15',
          dueDate: '2023-07-30',
          paidDate: null
        }
      ];
      
      setInvoices(mockInvoices);
    } catch (err) {
      setError('Failed to fetch invoices');
    } finally {
      setLoading(false);
    }
  };

  const handlePayment = async (invoiceId) => {
    // In a real app, this would be an API call to process payment
    // For now, we'll just update the local state
    setInvoices(prevInvoices => 
      prevInvoices.map(invoice => 
        invoice.id === invoiceId ? { ...invoice, status: 'paid', paidDate: new Date().toISOString().split('T')[0] } : invoice
      )
    );
  };

  if (loading) {
    return <div className="invoices-list">Loading invoices...</div>;
  }

  if (error) {
    return <div className="invoices-list error-message">{error}</div>;
  }

  return (
    <div className="invoices-list">
      <h2>My Invoices</h2>
      
      {invoices.length === 0 ? (
        <p>No invoices found.</p>
      ) : (
        <div className="invoices-table">
          <div className="table-header">
            <div className="header-item">Invoice ID</div>
            <div className="header-item">Appointment</div>
            <div className="header-item">Amount</div>
            <div className="header-item">Status</div>
            <div className="header-item">Issued Date</div>
            <div className="header-item">Due Date</div>
            <div className="header-item">Actions</div>
          </div>
          
          {invoices.map(invoice => (
            <div key={invoice.id} className="invoice-row">
              <div className="row-item">{invoice.id}</div>
              <div className="row-item">#{invoice.appointmentId}</div>
              <div className="row-item">${invoice.amount.toFixed(2)}</div>
              <div className="row-item">
                <span className={`invoice-status status-${invoice.status}`}>
                  {invoice.status}
                </span>
              </div>
              <div className="row-item">{invoice.issuedDate}</div>
              <div className="row-item">{invoice.dueDate}</div>
              <div className="row-item">
                {invoice.status === 'pending' || invoice.status === 'overdue' ? (
                  <button 
                    className="btn btn-success"
                    onClick={() => handlePayment(invoice.id)}
                  >
                    Pay Now
                  </button>
                ) : invoice.status === 'paid' ? (
                  <span>Paid on {invoice.paidDate}</span>
                ) : null}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default InvoiceList;